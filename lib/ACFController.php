<?php
/**
 *  Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo;

use \TMS\Theme\Base;
/**
 * Class ACFController
 *
 * @package TMS\Theme\Taidemuseo
 */
class ACFController extends Base\ACFController implements Base\Interfaces\Controller {

    /**
     * Get ACF base dir
     *
     * @return string
     */
    protected function get_base_dir() : string {
        return __DIR__ . '/ACF';
    }

}
