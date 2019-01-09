<?php

namespace Concrete\Package\CommunityStoreShippingPostcode;

use Package;
use Whoops\Exception\ErrorException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;

class Controller extends Package
{
    protected $pkgHandle = 'community_store_shipping_postcode';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.0';

    protected $pkgAutoloaderRegistries = array(
        'src/CommunityStore' => 'Concrete\Package\CommunityStoreShippingPostcode\Src\CommunityStore',
    );

    public function getPackageDescription()
    {
        return t("Community Store Shipping Method allowing pricing per postcode");
    }

    public function getPackageName()
    {
        return t("Community Store Postcode Based Shipping");
    }

    public function install()
    {
        $installed = Package::getInstalledHandles();
        if(!(is_array($installed) && in_array('community_store',$installed)) ) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        } else {
            $pkg = parent::install();
            StoreShippingMethodType::add('postcode', 'Postcode based shipping', $pkg);
        }

    }
    public function uninstall()
    {
        $pm = StoreShippingMethodType::getByHandle('postcode');
        if ($pm) {
            $pm->delete();
        }
        $pkg = parent::uninstall();
    }

}
?>