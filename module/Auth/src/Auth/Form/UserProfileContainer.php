<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/**  */
namespace Auth\Form;

use Core\Form\Container;

/**
 *
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class UserProfileContainer extends Container
{
    public function init()
    {
        $this->setForms([
            'info' => 'Auth/UserInfoContainer',

            'base' => [
                'type' => 'Auth/UserBase',
                'label' => /*@translate*/ 'General settings',
                'property' => true,
            ],
        ]);
    }
}
