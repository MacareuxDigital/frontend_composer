<?php
namespace Concrete\Package\FrontendComposer;

use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $appVersionRequired = '8.5.1';
    protected $pkgHandle = 'frontend_composer';
    protected $pkgVersion = '0.0.1';

    public function getPackageName()
    {
        return t('Frontend Composer');
    }

    public function getPackageDescription()
    {
        return t('Make it enables to create pages from front end of your site.');
    }

    public function install()
    {
        $pkg = parent::install();
        $this->installContentFile('config/blocktypes.xml');
        $this->installContentFile('config/permissions.xml');

        return $pkg;
    }
}
