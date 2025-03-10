<?php

namespace Iqionly\Laraddon\Tests\Addons\Test\Controllers;

use Illuminate\Routing\Controller;
use Laraddon\Attributes\Route;

class AddonTestController extends Controller
{
    /**
     * Test For Route with parameter index and model bindings
     * 
     * @param string $index
     * @param User $user
     * 
     * @return string
     * 
     */
    public function test_route_model($user)
    {
        return 'Web Test Controller';
    }
    
    /**
     * Test for Route with Attributes
     *
     * @return string
     * 
     */
    #[Route(any: 'test_route_attribute')]
    public function test_route_attribute()
    {
        return 'Hello from Addon Test Controller';
    }

    /**
     * Test for Route without Attributes for using default Attribute
     *
     * @return string
     * 
     */
    public function test_default_attribute()
    {
        return 'Hello from Addon Test Controller';
    }

    /**
     * Test For Route with parameter index and model bindings
     *
     * @param string $index
     * @param User $user
     * 
     * @return string
     * 
     */
    public function test_route_parameter(string $index, $user)
    {
        return 'Web Test Controller';
    }
}
