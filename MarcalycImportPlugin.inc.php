<?php

/**
 * @file plugins/importexport/marcalycImporter/MarcalycImportPlugin.inc.php
 *
 * Copyright (c) 2019 
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarcalycImportPlugin
 * @ingroup plugins_importexport_marcalycImporter
 *
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');

class MarcalycImportPlugin extends ImportExportPlugin {

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'MarcalycImportPlugin';
	}

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.marcalycImporter.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.marcalycImporter.description');
	}

	/**
	 * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
	 */
	function getPluginSettingsPrefix() {
		return 'marcalycImporter';
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		parent::display($args, $request);
		$templateMgr = TemplateManager::getManager($request);
		$journal = $request->getJournal();
		switch (array_shift($args)) {
			case 'index':
			case '':
				$templateMgr->display($this->getTemplateResource('index.tpl'));
				break;

			case 'importCompressedFile':
				$json = new JSONMessage(true);
				$json->setEvent('addTab', array(
					'title' => __('plugins.importexport.native.results'),
					'url' => $request->url(null, null, null, array('plugin', $this->getName(), 'import'), array('temporaryFileId' => $request->getUserVar('temporaryFileId'))),
				));
				header('Content-Type: application/json');
				return $json->getString();
			break;

			case 'uploadCompressedFile':
				$user = $request->getUser();
				import('lib.pkp.classes.file.TemporaryFileManager');
				$temporaryFileManager = new TemporaryFileManager();
				$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
				if ($temporaryFile) {
					$json = new JSONMessage(true);
					$json->setAdditionalAttributes(array(
						'temporaryFileId' => $temporaryFile->getId()
					));
				} else {
					$json = new JSONMessage(false, __('common.uploadFailed'));
				}
				header('Content-Type: application/json');
				return $json->getString();
			break;

			case 'import':

				AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);
				$temporaryFileId = $request->getUserVar('temporaryFileId');
				$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
				$user = $request->getUser();
				$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());

				if (!$temporaryFile) {
					$json = new JSONMessage(true, __('plugins.inportexport.native.uploadFile'));
					header('Content-Type: application/json');
					return $json->getString();
				}

				$temporaryFilePath = $temporaryFile->getFilePath();
				$templateMgr->assign('temporaryFilePath', $temporaryFilePath);



				$errorMsg = null;

				//$destFile = $temporaryFilePath;
				//rename($temporaryFilePath, $destFile);

				$processingFilePath = $this->decompressZipFile($temporaryFilePath, $errorMsg);


				$templateMgr->assign('temporaryFolder', '->' . $processingFilePath. ' -> ' . $errorMsg);





				$json = new JSONMessage(true, $templateMgr->fetch($this->getTemplateResource('results.tpl')));
				header('Content-Type: application/json');
				return $json->getString();

			break;

			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}



private function decompressZipFile($filePath, &$errorMsg) {
		PKPLocale::requireComponents(LOCALE_COMPONENT_PKP_ADMIN);
		$unzipPath = Config::getVar('cli', 'unzip');
		//$tempPath = Config::getVar('files', 'files_dir') . '/temp/';
		if (!is_executable($unzipPath)) {
			$errorMsg = __('admin.error.executingUtil', array('utilPath' => $unzipPath, 'utilVar' => 'unzip'));
			return false;
		}
		$unzipCmd = escapeshellarg($unzipPath);
		$unzipCmd .= ' -o';
		$unzipCmd .= ' -d ' . $filePath . '_1';
		$output = array($filePath);
		$returnValue = 0;
		$unzipCmd .= ' ' . $filePath;
		if (!Core::isWindows()) {
			$unzipCmd .= ' 2>&1';
		}
		exec($unzipCmd, $output, $returnValue);
		if ($returnValue > 0) {
			$errorMsg = __('admin.error.utilExecutionProblem', array('utilPath' => $unzipPath, 'output' => implode(PHP_EOL, $output)));
			return false;
		}

		return $filePath . '_1';
		
	}





        /**
         * @copydoc PKPImportExportPlugin::usage
         */
        function usage($scriptName) {
                echo __('plugins.importexport.marcalycImporter.cliUsage', array(
                        'scriptName' => $scriptName,
                        'pluginName' => $this->getName()
                )) . "\n";
        }


                /**
         * @see PKPImportExportPlugin::executeCLI()
         */
        function executeCLI($scriptName, &$args) {
                $this->usage($scriptName);
        }


}


