<?php

    /**#@+
     * include SimpleTest files
     */
    //require_once(dirname(__FILE__) . '/../dumper.php');
    //require_once(dirname(__FILE__) . '/../compatibility.php');
    require_once(dirname(__FILE__) . '/../test_case.php');
    require_once(dirname(__FILE__) . '/../expectation.php');
    require_once(dirname(__FILE__) . '/../browser.php');
    require_once(dirname(__FILE__) . '/../collector.php');
    require_once(dirname(__FILE__) . '/../web_tester.php');
	/**#@-*/

class FeatureContext {

	public static $_pages = array(
		'the overview page' => '/',
		'my profile page' => '.',
		'the login page' => '/users/login'

	);
	function initialize() {
		self::addStep(
			'Given', '/I am on (.*)/', 
			function (&$browser, $matches, $args, $label) {
				$browser->assertTrue($browser->get(FeatureContext::$_pages[$matches[1]]), $label);
			});

		self::addStep(
			'When', '/I go to (.*)/', 
			function (&$browser, $matches, $args, $label) {
				$browser->assertTrue($browser->get(FeatureContext::$_pages[$matches[1]]), $label);
			});

		self::addStep('When', '/I fill "(.*)" into the "(.*)" field/',
			function (&$browser, $matches, $args, $label) {
				$browser->assertTrue($browser->setField($matches[2], $matches[1]), $label);
			});

		self::addStep('When', '/I click on "(.*)"/',
			function (&$browser, $matches, $args, $label) {
				$browser->assertTrue($browser->click($matches[1]), $label);
			});

		self::addStep('Then', '/I should be on (.*)/',
			function (&$browser, $matches, $args, $label) {
				$url_path = parse_url($browser->getUrl(), PHP_URL_PATH);
				$browser->assertEqual($url_path, FeatureContext::$_pages[$matches[1]], $label);
			});

		self::addStep('Then', '/I should see "(.*)"/',
			function(&$browser, $matches, $args, $label) {
				$browser->assertText($matches[1], $label);
			});

		self::addStep('Then', '/the title of the page should be "(.*)"/',
			function(&$browser, $matches, $args, $label) {
				$browser->assertTitle($matches[1], $label);
			});

		self::addStep('Then', '/there should exist an element with id "(.*)"/',
			function(&$browser, $matches, $args, $label) {
				$browser->assertPattern('/id ?= ?[\'"]' . $matches[1] . '[\'"]/', $label);
			});

		self::addStep('Then', '/there should be a link matching "(.*)" labeled "(.*)"/',
			function(&$browser, $matches, $args, $label) {
				$pattern = str_replace('/', '\/', $matches[1]);
				$browser->assertLink($matches[2], new PatternExpectation('/' . $pattern . '/'), $label);
			});

		self::addStep('Given', '/I am logged in as an (administrator|manager|employeee)/',
			function(&$browser, $matches, $args, $label) {
				$browser->assertTrue($browser->get(FeatureContext::$_pages['the login page']), $label);
				switch ($matches[1]) {
					case 'administrator':
						$username = 'admin';
						$password = '123123';
						break;
					default:
						$this->assertTrue(false, $label . ' -- ' . $matches[1] . ' credentials not implemented');
				}
				$browser->assertTrue($browser->setField('data[User][username]', $username), $label);
				$browser->assertTrue($browser->setField('data[User][password]', $password), $label);
				$browser->assertTrue($browser->click('Sign In'), $label);
			});


		self::$_initialized = true;
	}

	private static $_initialized = false;

	private static $_steps = array();

	static function addStep($type, $regex, $callback) {
		self::$_steps[$regex] = $callback;
	}

	static function step(&$browser, $step) {
		if (!self::$_initialized)
			self::initialize();
		foreach(array_keys(self::$_steps) as $regex) {
			if(preg_match($regex, $step->getText(), $matches)) {
				$callback = self::$_steps[$regex];
				$callback($browser, $matches, $step->getArguments(), $step->getType() . ' ' . $step->getText() . ' %s');
				return;
			}
		}
		$browser->assertTrue(false, $step->getText() . " not implemented");
	}
}

class FeatureTestSuite extends TestSuite {
	function FeatureTestSuite($label = false) {
		$this->TestSuite($label);
	}

	function addFile($feature_file) {
		$loader = new FeatureFileLoader();
		$this->add($loader->load($feature_file));
	}
}

class FeatureFileLoader {
	function load($feature_file) {
		App::import('Vendor', 'gherkin');
		$keywords = new Behat\Gherkin\Keywords\CachedArrayKeywords(ROOT.DS.APP_DIR.DS.'vendors/gherkin/i18n.php');
		$lexer  = new Behat\Gherkin\Lexer($keywords);
		$parser = new Behat\Gherkin\Parser($lexer);

		$feature = $parser->parse(file_get_contents($feature_file));

		$case = new FeatureTestCase($feature->getTitle());
		$case->setBackground($feature->getBackground());
		$case->setScenarios($feature->getScenarios());

		return $case;
	}
}

class FeatureTestCase extends CakeWebTestCase {
	var $_background;
	var $_scenarios;

	/**
	 *    Constructor. Sets the test name.
	 *    @param $label        Test name to display.
	 *    @public
	 */
	function FeatureTestCase($label = false) {
		$this->SimpleTestCase($label);
	}

	function setBackground($background) {
		$this->_background = $background;
	}

	function setScenarios($scenarios) {
		$this->_scenarios = $scenarios;
	}

	function run(&$reporter) {
		// TODO: Do something with the background
		parent::run($reporter);
	}

	function getTests() {
		$test_methods = array();
		foreach(array_keys($this->_scenarios) as $num) {
			$test_methods[] = 'test' . $num;
		}
		return $test_methods;
	}

	function __call($name, $arguments) {
		if(!preg_match('/^test(\d+)$/', $name, $matches)) {
			throw new Exception('Called invalid Test Case: ' . $name);
		}

		$scenario_num = intval($matches[1]);

		$this->runScenario($this->_scenarios[$scenario_num]);
	}

	function runScenario($scenario) {
		$this->get('http://localdev.mytribehr.com/');
		foreach($scenario->getSteps() as $step)
			$this->runStep($this, $step);
	}

	function runStep(&$browser, $step) {
		FeatureContext::step($browser, $step);
	}
}

SimpleTest::ignore('FeatureTestCase');
