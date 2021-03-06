<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

/**
 * Handles theme view.xml files
 */
namespace Magento\View;

use Magento\Filesystem\Directory\ReadInterface;

class Config implements \Magento\View\ConfigInterface
{
    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $viewConfigs = array();

    /**
     * Module configuration reader
     *
     * @var \Magento\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var \Magento\View\Service
     */
    protected $viewService;

    /**
     * View file system model
     *
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var \Magento\Config\FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @param \Magento\Module\Dir\Reader $moduleReader
     * @param \Magento\Filesystem $filesystem
     * @param Service $viewService
     * @param FileSystem $viewFileSystem
     * @param \Magento\Config\FileIteratorFactory $fileIteratorFactory
     * @param $filename
     */
    public function __construct(
        \Magento\Module\Dir\Reader $moduleReader,
        \Magento\Filesystem $filesystem,
        \Magento\View\Service $viewService,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Config\FileIteratorFactory $fileIteratorFactory,
        $filename = self::CONFIG_FILE_NAME
    ) {
        $this->moduleReader = $moduleReader;
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\Filesystem::ROOT);
        $this->viewService = $viewService;
        $this->viewFileSystem = $viewFileSystem;
        $this->filename = $filename;
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return \Magento\Config\View
     */
    public function getViewConfig(array $params = array())
    {
        $this->viewService->updateDesignParams($params);
        /** @var $currentTheme \Magento\View\Design\ThemeInterface */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getId();
        if (isset($this->viewConfigs[$key])) {
            return $this->viewConfigs[$key];
        }

        $configFiles = $this->moduleReader->getConfigurationFiles($this->filename)->toArray();

        $themeConfigFile = $currentTheme->getCustomization()->getCustomViewConfigPath();
        if (empty($themeConfigFile) ||
            !$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($themeConfigFile))
        ) {
            $themeConfigFile = $this->viewFileSystem->getFilename(
                $this->filename, $params
            );
        }
        if ($themeConfigFile &&
            $this->rootDirectory->isExist($this->rootDirectory->getRelativePath($themeConfigFile))
        ) {
            $configFiles[$this->rootDirectory->getRelativePath($themeConfigFile)] =
                $this->rootDirectory->readFile($this->rootDirectory->getRelativePath($themeConfigFile));
        }
        $config = new \Magento\Config\View($configFiles);

        $this->viewConfigs[$key] = $config;
        return $config;
    }
}
