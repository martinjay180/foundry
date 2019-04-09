<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHLAK\Config\Config;
use Foundry\Paging;

final class PagingTest extends TestCase
{
    // Tests the default pageSize key
    public function testDefaultPageSize()
    {
        $p = new Paging();
        $p->SetParams(array("pageSize"=>50));
        $this->assertEquals($p->PageSize(), 50);
    }

    // Tests the a custom pageSize key
    public function testCustomPageSize()
    {
        $p = new Paging(array("pageSize"=>"ps"));
        $p->SetParams(array("ps"=>75));
        $this->assertEquals($p->PageSize(), 75);
    }

    // Tests the pageSize with start and end parameter
    public function testDefaultPageSizeFromStartAndEnd()
    {
        $p = new Paging();
        $p->SetParams(array("start"=>0,"end"=>10));
        $this->assertEquals($p->PageSize(), 10);
    }

    // Tests the pageSize with start and end parameter
    public function testDefaultPageStart()
    {
        $p = new Paging();
        $p->SetParams(array("start"=>10,"end"=>20));
        $this->assertEquals($p->Start(), 10);
        $this->assertEquals($p->PageSize(), 10);
    }

    // Tests the pageSize with start and end parameter
    public function testPageStartFromPageSizeAndOffset()
    {
        $p = new Paging();
        $p->SetParams(array("pageSize"=>25,"page"=>2));
        $this->assertEquals($p->Start(), 26);
        $this->assertEquals($p->End(), 50);
    }

    // Tests the default page
    public function testDefaultPage()
    {
        $p = new Paging();
        $p->SetParams(array("pageSize"=>10,"page"=>2));
        $this->assertEquals($p->Page(), 2);
    }

    // Tests the custom page
    public function testCustomPage()
    {
        $p = new Paging(array("page"=>"p"));
        $p->SetParams(array("pageSize"=>10,"p"=>2));
        $this->assertEquals($p->Page(), 2);
    }

    // Tests the page with start and end parameter
    public function testPageFromStartAndEnd()
    {
        $p = new Paging();
        $p->SetParams(array("start"=>25,"end"=>50));
        $this->assertEquals($p->Page(), 2);
        $p->SetParams(array("start"=>100,"end"=>125));
        $this->assertEquals($p->Page(), 5);
        $p->SetParams(array("start"=>0,"end"=>7));
        $this->assertEquals($p->Page(), 1);
    }

}
