<?php
use Codeception\Util\Stub;
use Ldap\Internal\LdapLink;

class LdapLinkTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;
    protected $l;   // The LdapLink instance being tested

    protected function _before()
    {
        $this->l = new LdapLink( '127.0.0.1' );
    }

    protected function _after()
    {
    }

    public function testMethodMapping()
    {
        $version = null;

        // Make sure the default is still what we expect...
        $this->l->get_option( LDAP_OPT_PROTOCOL_VERSION, $version );
        $this->assertSame( 2, $version );

        // Set an ldap option to some non-default value...
        $this->l->set_option( LDAP_OPT_PROTOCOL_VERSION, 3 );
        // Check that the value is indeed updated
        $this->l->get_option( LDAP_OPT_PROTOCOL_VERSION, $version );
        $this->assertSame( 3, $version );
    }
}
