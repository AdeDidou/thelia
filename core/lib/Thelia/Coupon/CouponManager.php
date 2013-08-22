<?php
/**********************************************************************************/
/*                                                                                */
/*      Thelia	                                                                  */
/*                                                                                */
/*      Copyright (c) OpenStudio                                                  */
/*      email : info@thelia.net                                                   */
/*      web : http://www.thelia.net                                               */
/*                                                                                */
/*      This program is free software; you can redistribute it and/or modify      */
/*      it under the terms of the GNU General Public License as published by      */
/*      the Free Software Foundation; either version 3 of the License             */
/*                                                                                */
/*      This program is distributed in the hope that it will be useful,           */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of            */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             */
/*      GNU General Public License for more details.                              */
/*                                                                                */
/*      You should have received a copy of the GNU General Public License         */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.      */
/*                                                                                */
/**********************************************************************************/

namespace Thelia\Coupon;

use Thelia\Coupon\Type\CouponInterface;

/**
 * Created by JetBrains PhpStorm.
 * Date: 8/19/13
 * Time: 3:24 PM
 *
 * Manage how Coupons could interact with a Checkout
 *
 * @package Coupon
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class CouponManager
{
    /** @var  CouponAdapterInterface Provide necessary value from Thelia*/
    protected $adapter;

    /** @var array CouponInterface to process*/
    protected $coupons = array();

    /**
     * Constructor
     * Gather Coupons from Adapter
     * via $adapter->getCurrentCoupons();
     *
     * @param CouponAdapterInterface $adapter Provide necessary value from Thelia
     */
    function __construct($adapter)
    {
        $this->adapter = $adapter;
        $this->coupons = $this->adapter->getCurrentCoupons();
    }


    /**
     * Get Discount for the given Coupons
     *
     * @api
     * @return float checkout discount
     */
    public function getDiscount()
    {
        $discount = 0.00;

        if (count($this->coupons) > 0) {
            $couponsKept = $this->sortCoupons();
            $isRemovingPostage = $this->isCouponRemovingPostage($couponsKept);

            if ($isRemovingPostage) {
                $postage = $this->adapter->getCheckoutPostagePrice();
                $discount -= $postage;
            }

            // Just In Case test
            if ($discount >= $this->adapter->getCheckoutTotalPrice()) {
                $discount = 0.00;
            }
        }

        return $discount;
    }

    /**
     * Check if there is a Coupon removing Postage
     *
     * @param array $couponsKept Array of CouponInterface sorted
     *
     * @return bool
     */
    protected function isCouponRemovingPostage(array $couponsKept)
    {
        $isRemovingPostage = false;

        /** @var CouponInterface $coupon */
        foreach ($couponsKept as $coupon) {
            if ($coupon->isRemovingPostage()) {
                $isRemovingPostage = true;
            }
        }

        return $isRemovingPostage;
    }

    /**
     * Sort Coupon to keep
     * Coupon not cumulative cancels previous
     *
     * @return array Array of CouponInterface sorted
     */
    protected function sortCoupons()
    {
        $couponsKept = array();

        /** @var CouponInterface $coupon */
        foreach ($this->coupons as $coupon) {
            if (!$coupon->isCumulative()) {
                $couponsKept = array();
                $couponsKept[] = $coupon;
            }
        }

        return $couponsKept;
    }
}