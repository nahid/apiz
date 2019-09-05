<?php

namespace Api;

use Apiz\Http\Response;

class NewResponse extends Response
{
    public function getCouponCode()
    {
        return $this->query('coupon_details')->get()->code;
    }
}