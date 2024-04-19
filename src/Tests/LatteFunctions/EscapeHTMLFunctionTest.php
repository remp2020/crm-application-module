<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Tests\LatteFunctions;

use Crm\ApplicationModule\LatteFunctions\EscapeHTMLFunction;
use PHPUnit\Framework\TestCase;
use function Crm\ApplicationModule\LatteFunctions\escapehtml;

class EscapeHTMLFunctionTest extends TestCase
{
    public function testEscapeHTMLFunction(): void
    {
        $input = 'This is a <strong style="color: red;" class=\'bold\'>test</strong>';
        $expectedOutput = 'This is a &lt;strong style=&quot;color: red;&quot; class=&#039;bold&#039;&gt;test&lt;/strong&gt;';

        $this->assertSame($expectedOutput, escapehtml($input));

        $escapeHTMLFunction = new EscapeHTMLFunction();
        $this->assertSame($expectedOutput, $escapeHTMLFunction->process($input));
    }
}
