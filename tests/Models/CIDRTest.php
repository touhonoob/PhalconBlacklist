<?php

namespace PhalconBlacklist\Tests;

use PhalconBlacklist\Models\CIDR;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date Mar 7, 2015
 */
class CIDRTest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        global $di;
        parent::tearDown();
        $table = (new CIDR())->getSource();
        $di['db']->query("DELETE FROM `{$table}` WHERE 1;");
        $di['db']->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1;");
    }

    public function testValidateCIDR()
    {
        $this->validateParts(CIDR::validateCIDR("168.143.113.0/32"));
        $this->validateParts(CIDR::validateCIDR("168.143.113.0/16"));
    }

    private function validateParts($parts)
    {
        $this->assertInternalType('integer', $parts['ip']);
        $this->assertInternalType('integer', $parts['broadcast']);
    }

    /**
     *  @expectedException \InvalidArgumentException
     */
    public function testValidateCIDR_7()
    {
        CIDR::validateCIDR("0.0.0.0/7");
    }

    /**
     *  @expectedException \InvalidArgumentException
     */
    public function testValidateCIDR_33()
    {
        CIDR::validateCIDR("0.0.0.0/33");
    }

    public function testCalculateLast()
    {
        $parts = CIDR::validateCIDR("168.143.113.0/16");
        $last = CIDR::calculateLast($parts['ip'], $parts['broadcast']);
        $this->assertEquals(ip2long("168.143.255.255"), $last);
    }

    public function testCreateFromCIDR()
    {
        $cidr = CIDR::createFromCIDR("168.143.113.0/16");
        $this->assertEquals(ip2long("168.143.113.0"), $cidr->first);
        $this->assertEquals(ip2long("168.143.255.255"), $cidr->last);
    }

    public function testCreateFromIP()
    {
        $faker = \Faker\Factory::create();
        $ip = $faker->ipv4;
        $cidr = CIDR::createFromIP($ip);
        $this->assertEquals(ip2long($ip), $cidr->first);
        $this->assertEquals(ip2long($ip), $cidr->last);
    }

    public function testCheckIP()
    {
        $cidr = $this->randCIDR();
        for ($i = $cidr->first; $i <= $cidr->last; $i++) {
            $ip = long2ip($i);
            $this->assertTrue(CIDR::checkIP($ip));
        }
    }

    public function testGarbageCollection()
    {
        $cidr = $this->randCIDR();
        $cidr->expire_timestamp = time() - 10;
        $cidr->update();
        CIDR::garbageCollection();
        $this->assertFalse(CIDR::findFirst($cidr->id));
    }

    public function testRemove()
    {
        $cidr = $this->randCIDR();
        CIDR::remove($cidr->id);
        $this->assertFalse(CIDR::findFirst($cidr->id));
    }

    private function randCIDR()
    {
        $faker = \Faker\Factory::create();
        $mask = rand(24, 32);
        $ip = $faker->ipv4;

        $cidr = CIDR::createFromCIDR("$ip/$mask");
        $cidr->create();

        return $cidr;
    }
}
