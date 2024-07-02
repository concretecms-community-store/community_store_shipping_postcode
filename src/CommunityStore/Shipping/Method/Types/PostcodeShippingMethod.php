<?php
namespace Concrete\Package\CommunityStoreShippingPostcode\Src\CommunityStore\Shipping\Method\Types;

use Core;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStorePostcodeMethods")
 */
class PostcodeShippingMethod extends ShippingMethodTypeMethod
{
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $minimumAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $maximumAmount;

    /**
     * @ORM\Column(type="string")
     */
    protected $country;

    public function setMinimumAmount($minAmount)
    {
        $this->minimumAmount = $minAmount;
    }
    public function setMaximumAmount($maxAmount)
    {
        $this->maximumAmount = $maxAmount;
    }

    public function getMinimumAmount()
    {
        return $this->minimumAmount;
    }
    public function getMaximumAmount()
    {
        return $this->maximumAmount;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getShippingMethodTypeName() {
        return t('Postcode based shipping');
    }

    public function addMethodTypeMethod($data)
    {
        return $this->addOrUpdate('add', $data);
    }

    public function update($data)
    {
        return $this->addOrUpdate('update', $data);
    }

    private function addOrUpdate($type, $data)
    {
        if ($type == "update") {
            $sm = $this;
        } else {
            $sm = new self();
        }
        // do any saves here
        //$sm->setRate($data['rate']);

        $sm->setCountry($data['country']);
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);

        $em = Database::connection()->getEntityManager();
        $em->persist($sm);
        $em->flush();

        $rates = array();

        $count = 0;

        if (!empty($this->post('postcodes'))) {
            foreach ($this->post('postcodes') as $pc) {

                if ($pc) {
                    $rates[] = array('postcodes' => $pc, 'label' => $this->post('label')[$count], 'rate' => $this->post('rate')[$count], 'free' => $this->post('free')[$count],  'expresslabel' => $this->post('expresslabel')[$count], 'expressrate' => $this->post('expressrate')[$count], 'freeexpress' => $this->post('freeexpress')[$count]);
                }
                $count++;
            }
        }

        \Config::save('community_store_shipping_postcode.' . 'shipping_method_' . $sm->getShippingMethodTypeMethodID() , $rates);

        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form', Core::make("helper/form"));
        $this->set('smt', $this);
        if (is_object($shippingMethod)) {
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
            $rates = \Config::get('community_store_shipping_postcode.' . 'shipping_method_' . $smtm->getShippingMethodTypeMethodID());
        } else {
            $smtm = new self();
            $rates = array();
        }

        $this->set("smtm", $smtm);
        $this->set('rates', $rates);
    }


    public function isEligible()
    {
        $customer = new StoreCustomer();
        $custCountry = $customer->getValue('shipping_address')->country;

        if ($custCountry == $this->getCountry()) {
            $subtotal = StoreCalculator::getSubTotal();
            $max = $this->getMaximumAmount();
            if ($max != 0) {
                if ($subtotal >= $this->getMinimumAmount() && $subtotal <= $this->getMaximumAmount()) {
                    return true;
                } else {
                    return false;
                }
            } elseif ($subtotal >= $this->getMinimumAmount()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function findMatch() {
        $customer = new StoreCustomer();

        $shippingpostcode = $customer->getAddressValue('shipping_address', 'postal_code');

        $reg = '^(((([A-Z][A-Z]{0,1})[0-9][A-Z0-9]{0,1}) {0,}[0-9])[A-Z]{2})$^';
        preg_match($reg, $shippingpostcode, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[3][0])) {
            $shippingpostcode = $matches[3][0];
        }

        $allrates = \Config::get('community_store_shipping_postcode.' . 'shipping_method_' . $this->getShippingMethodTypeMethodID());

        $finalrate = false;
        $finalexpressrate = false;
        $finallabel = '';
        $finalexpresslabel = '';
        $finalfree = '';
        $finalexpressfree = '';

        foreach($allrates as $rate) {
            $postcodes = explode(',', str_replace(array(' to ', 'to', ' - '), '-', $rate['postcodes']));

            foreach($postcodes as $postcode) {
                $postcode = trim($postcode);
                $postcode = explode('-', $postcode);

                $found = false;

                if (count($postcode) == 2) {
                    if ($shippingpostcode >= $postcode[0] && $shippingpostcode <= $postcode[1] ) {
                        $found = true;
                    }
                } else {
                    if ($postcode[0] == $shippingpostcode) {
                        $found = true;
                    }
                }

                if ($found) {
                    $finalrate = $rate['rate'];
                    $finalexpressrate = $rate['expressrate'];
                    $finallabel = $rate['label'];
                    $finalexpresslabel = $rate['expresslabel'];
                    $finalfree = $rate['free'];
                    $finalexpressfree = $rate['freeexpress'];
                }
            }

        }

        return array('rate'=>$finalrate, 'label'=>$finallabel, 'free'=>$finalfree, 'expressrate'=>$finalexpressrate, 'expresslabel'=>$finalexpresslabel, 'expressfree'=>$finalexpressfree);
    }


    public function getOffers() {
        $subtotal = StoreCalculator::getSubTotal();

        $offers = array();

        $rate = $this->findMatch();
        $expressfree = false;

        if ($rate['expressrate'] != '') {
            $offer = new StoreShippingMethodOffer();

            if ($rate['expressfree'] != '' && $subtotal >= $rate['expressfree']) {
                $offer->setRate(0);
                $expressfree = true;
            } else {
                $offer->setRate($rate['expressrate']);
            }

            $offer->setOfferLabel($rate['expresslabel']);
            $offers[] = $offer;
        }

        if ($rate['rate'] != '' && !$expressfree) {
            $offer = new StoreShippingMethodOffer();

            if ($rate['free'] != '' && $subtotal >= $rate['free']) {
                $offer->setRate(0);
            } else {
                $offer->setRate($rate['rate']);
            }

            $offer->setOfferLabel($rate['label']);
            $offers[] = $offer;
        }

        return $offers;
    }


}
