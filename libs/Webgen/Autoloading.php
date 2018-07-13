<?php
	/**
	 * Webgen - static website generator written in PHP
	 *
	 * @author  Jan Pecha, <janpecha@email.cz>
	 */

	namespace Webgen;

	use Nette\Utils;

	class Autoloading
	{
		use \Nette\SmartObject;

		private $composerFile;

		private $libsDir;


		public function __construct($composerFile, $libsDir)
		{
			$this->composerFile = $composerFile;
			$this->libsDir = $libsDir;
		}



		public function autoload()
		{
			$files = array();

			// Composer
			if ($this->composerFile !== NULL) {
				$json = @file_get_contents($this->composerFile);

				if (is_string($json)) {
					$data = Utils\Json::decode($json, Utils\Json::FORCE_ARRAY);
					$composerDir = 'vendor';

					if (isset($data['config']['vendor-dir'])) {
						$composerDir = $data['config']['vendor-dir'];
					}

					$files[] = dirname($this->composerFile) . "/$composerDir/autoload.php";
				}
			}

			// Own loader
			$files[] = $this->libsDir . '/autoload.php';

			// Include files
			if (count($files)) {
				\Cli\Cli::log('Autoload files...');

				foreach ($files as $file) {
					if (file_exists($file)) {
						\Cli\Cli::log($file);
						LimitedScope::load($file);
					} else {
						\Cli\Cli::log("[not found] $file");
					}
				}
			}

			return $files;
		}


		/**
		 * @return \Nette\Loaders\RobotLoader|FALSE
		 */
		public function createRobotLoader()
		{
			if (!file_exists($this->libsDir)) {
				return FALSE;
			}

			$loader = new \Nette\Loaders\RobotLoader;
			$loader->setAutoRefresh(TRUE);
			$loader->addDirectory($this->libsDir);
			return $loader;
		}
	}

