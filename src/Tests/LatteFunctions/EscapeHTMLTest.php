<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Tests\LatteFunctions;

use Crm\ApplicationModule\LatteFunctions\EscapeHTML;
use PHPUnit\Framework\TestCase;

class EscapeHTMLTest extends TestCase
{
    public function testEscapeHTMLFunction(): void
    {
        $input = 'This is a <strong style="color: red;" class=\'bold\'>test</strong>';
        $expectedOutput = 'This is a &lt;strong style=&quot;color: red;&quot; class=&#039;bold&#039;&gt;test&lt;/strong&gt;';

        $this->assertSame($expectedOutput, EscapeHTML::escape($input));
    }
}
