<?php
use Codeception\Util\Stub;
use Ldap\Internal\LookupRequest;

/**
 * Class used as a demo implementation for the LookupRequest abstract class
 */
class ExampleRequest extends LookupRequest
{
  protected static $action = 'example_action';
}

class LookupRequestTest extends \Codeception\TestCase\Test
{
   /**
  * @var \CodeGuy
  */
  protected $codeGuy;
  protected $b        = 'dc=example,dc=com';
  protected $f        = '(objectClass=user)';
  protected $g        = ['name', 'objectClass'];
  protected $s        = 100;
  protected $t        = 10;
  protected $c        = 'some_random_value';


  protected function _before()
  {
  }

  protected function _after()
  {
  }

  public function testGettersAndSetters()
  {
    $req = new ExampleRequest;

    $req->base(       $this->b          );
    $req->filter(     $this->f          );
    $req->attributes( $this->g          );
    $req->attrsOnly(  true              );
    $req->sizeLimit(  $this->s          );
    $req->timeLimit(  $this->t          );
    $req->deref(      LDAP_DEREF_ALWAYS ); // = 3
    $req->cookie(     $this->c          );

    $this->assertSame( $req->base(),       $this->b          );
    $this->assertSame( $req->filter(),     $this->f          );
    $this->assertSame( $req->attributes(), $this->g          );
    $this->assertSame( $req->attrsOnly(),  true              );
    $this->assertSame( $req->sizeLimit(),  $this->s          );
    $this->assertSame( $req->timeLimit(),  $this->t          );
    $this->assertSame( $req->deref(),      LDAP_DEREF_ALWAYS );
    $this->assertSame( $req->cookie(),     $this->c          );
  }

  public function testRequestAcceptsAttributesAsArrayOrString()
  {
    $req = new ExampleRequest;

    $req->attributes( ['name', 'cn'] );
    $this->assertSame( ['name', 'cn'], $req->attributes() );

    $req->attributes( 'name' );
    $this->assertSame( ['name'], $req->attributes(), 'A string should be typecast to array automatically' );
  }

  public function testRequestFluentMethods()
  {
    $req = new ExampleRequest;

    $req->from(        $this->b )
        ->where(       $this->f )
        ->get(         $this->g )
        ->limitTo(     $this->s )
        ->perPage()
        ->within(      $this->t )->secs();

    $this->assertSame( $this->b, $req->base() );
    $this->assertSame( $this->f, $req->filter() );
    $this->assertSame( $this->g, $req->attributes() );
    $this->assertSame( $this->s, $req->pageSize() );
    $this->assertTrue(           $req->pagedSearch() );
    $this->assertSame( null,     $req->sizeLimit(), 'SizeLimit should not be set when paged search is enabled' );
    $this->assertSame( $this->t, $req->timeLimit() );
  }
}
