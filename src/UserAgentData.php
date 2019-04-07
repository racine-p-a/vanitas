<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * Class UserAgentData
 * The class gets all informations from the user-agent using Wolfcast'browserDetection.
 *
 * @link https://github.com/racine-p-a/vanitas
 */
require_once __DIR__ . '/Visitor.php';

class UserAgentData
{
    private $_currentVisitor;

    /**
     * IPData constructor.
     * @param Visitor $visitor
     * @throws Exception
     */
    public function __construct(Visitor &$visitor)
    {
        $this->_currentVisitor = $visitor;
        $this->extractData();
    }

    private function extractData()
    {
        require_once __DIR__ . '/BrowserDetection.php';
        $browser = new Wolfcast\BrowserDetection();
        $this->_currentVisitor->setBrowserName($browser->getName());
        $this->_currentVisitor->setBrowserVersion($browser->getVersion());
        $this->_currentVisitor->setProcessorDesign($browser->is64bitPlatform());
        $this->_currentVisitor->setMobileDevice($browser->isMobile());
        $this->_currentVisitor->setRobot($browser->isRobot());
        $this->_currentVisitor->setOS($browser->getPlatform());
        $this->_currentVisitor->setOSVersion($browser->getPlatformVersion(true));
        $this->_currentVisitor->setOSName($browser->getPlatformVersion());
    }
}