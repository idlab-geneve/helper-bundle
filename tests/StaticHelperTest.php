<?php

namespace Idlab\HelperBundle\Tests;

use Idlab\HelperBundle\StaticHelper;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use PhpMyAdmin\SqlParser\Statements\DropStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\ReplaceStatement;
use PhpMyAdmin\SqlParser\Statements\SetStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;
use PHPUnit\Framework\TestCase;

class StaticHelperTest extends TestCase
{
    public function testCleanPhoneNumber(): void
    {
        $this->assertEquals('+41 21 123 12 21', StaticHelper::cleanPhoneNumber(' 021.123.12.21 '));
        $this->assertEquals('+41 21 784 54 12', StaticHelper::cleanPhoneNumber(' 21. 784 54.12 ', true, 'CH'));
        $this->assertEquals('+41211231221', StaticHelper::cleanPhoneNumber(' 021 123-1221', false));
        $this->assertEquals('+41 21 123 12 21', StaticHelper::cleanPhoneNumber(' +41(21)1231221'));
        $this->assertEquals('+41 21 123 12 21', StaticHelper::cleanPhoneNumber(' +41(0)211231221'));
        $this->assertEquals('+41 21 123 12 21', StaticHelper::cleanPhoneNumber(' +41 (0)21 123 12 21'));
        $this->assertEquals('+41 21 123 12 21', StaticHelper::cleanPhoneNumber(' (021) 1.2-3..122-1'));
        $this->assertEquals('+33 6 12 34 56 78', StaticHelper::cleanPhoneNumber('00336 12 34 56 78'));
        $this->assertEquals('+33 6 12 34 56 78', StaticHelper::cleanPhoneNumber('+336,12.34-56 78'));
        $this->assertEquals('+34 124 334 121', StaticHelper::cleanPhoneNumber('0034.12-4334-12.1'));
        $this->assertEquals(null, StaticHelper::cleanPhoneNumber(null));
    }

    public function testCleanNAVS13(): void
    {
        // test valid NACS13 756.8273.1938.10
        $this->assertEquals('756.8273.1938.10', StaticHelper::cleanNAVS13('756.8273.1938.10'));
        $this->assertEquals('756.8273.1938.10', StaticHelper::cleanNAVS13('7 56.82-73.19,38.1/0 '));
        // test invalid NACS13 756.8273.1938.11
        $this->assertNull(StaticHelper::cleanNAVS13('756.8273.1938.11'));
        $this->assertNull(StaticHelper::cleanNAVS13(null));
        $this->assertNull(StaticHelper::cleanNAVS13(null, true));
        $this->assertNull(StaticHelper::cleanNAVS13(null, false, '!!'));
        $this->assertEquals('!!756.8273.1938.11', StaticHelper::cleanNAVS13('756.8273.1938.11', false, '!!'));
        $this->assertEquals('756.8273.1938.10', StaticHelper::cleanNAVS13('756.8273.1938.18', true));
        $this->assertEquals('756.1725.5138.22', StaticHelper::cleanNAVS13(7561725513825, true));
    }

    public function testAreNumbersInStringSame(): void
    {
        $this->assertTrue(StaticHelper::areNumbersInStringSame(' 75 6 8 e2.!73 1ncha938 10 ', '75 6.82-73.1938.10 '));
        $this->assertFalse(StaticHelper::areNumbersInStringSame('7153676152', '7153676153'));
        $this->assertTrue(StaticHelper::areNumbersInStringSame('7153676152', '0007153676152'));
        $this->assertTrue(StaticHelper::areNumbersInStringSame(1234, 1234));
        $this->assertTrue(StaticHelper::areNumbersInStringSame('a', 'b'));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(null, false));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(null, true));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(true, false));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(true, null));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(false, true));
        $this->assertFalse(StaticHelper::areNumbersInStringSame(false, null));
        $this->assertTrue(StaticHelper::areNumbersInStringSame(true, true));
        $this->assertTrue(StaticHelper::areNumbersInStringSame(false, false));
        $this->assertTrue(StaticHelper::areNumbersInStringSame(null, null));
    }

    public function testExtractIban(): void
    {
        $this->assertEquals('CH43 09000 000176532113', StaticHelper::extractIban('CH43 0900 0000 1765 321 13  BIC POFICHBEXXX'));
        $this->assertEquals('CH43 0900 0000 1765 3s23 3', StaticHelper::extractIban('CH43 0900 0000 1765 3s23 3  BIC POFICHBEXXX', 2));
        $this->assertEquals('CH97 09000 000324247768', StaticHelper::extractIban('CCP 12-284276-8 IBAN CH97 0900 0000 3242 4776 8', 1));
        $this->assertEquals('CH49 00241 23140441240N', StaticHelper::extractIban('UBS 240-403312.40N  IBAN:CH49 0024 1231 4044 1240N', 1));
        $this->assertEquals('FR76 15589 56915 54154539240 21', StaticHelper::extractIban('CMBRFR2BARK FR76 1558 95 69 1554 1545 3924 021', 1));
        $this->assertEquals('FR48 20041 01017 0932237Y028 79', StaticHelper::extractIban('FR48 2004 1010 1709 3223 7Y02 879', 1));
        $this->assertEquals('FR94 20041 01017 0843163k028 49', StaticHelper::extractIban('FR94 2004 1010 1708 4316 3k02 849 BIC PSSTFRPPGRE', 1));
        $this->assertEquals('FR76 3546 6008 1096 7094 0709 835', StaticHelper::extractIban('12-249809-4 ou FR76 3546 6008 1096 7094 0709 835', 2));
        $this->assertEquals('FR19 2004 6422 0520 8274 7A02 602', StaticHelper::extractIban('PSSTFRPPLIL FR19 2004 6422 0520 8274 7A02 602', 2));
        $this->assertNull(StaticHelper::extractIban('PSSTFRPPLIL FX19 2004 6422 0520 8274 7A02 602', 2));
        $this->assertNull(StaticHelper::extractIban(null));
    }

    public function testExtractBic(): void
    {
        $this->assertEquals('POFICHBEXXX', StaticHelper::extractBic('CH43 0900 0000 1765 321 13  BIC POFICHBEXXX'));
        $this->assertEquals('POFICHBEXXX', StaticHelper::extractBic('17-123321-8'));
        $this->assertEquals('POFICHBEXXX', StaticHelper::extractBic('17-12-8'));
        $this->assertEquals('POFICHBEXXX', StaticHelper::extractBic('CCP 12-284276-8 IBAN CH97 0900 0000 3242 4776 8'));
        $this->assertEquals('PSSTFRPPGRE', StaticHelper::extractBic('FR94 2004 1010 1708 4316 3k02 849 BIC PSSTFRPPGRE'));
        $this->assertEquals('POFICHBEXXX', StaticHelper::extractBic('CCP 12-329186-4'));
        $this->assertNull(StaticHelper::extractBic('PSSTFRPPLIL FX19 2004 6422 0520 8274 7A02 602'));
        $this->assertNull(StaticHelper::extractBic(null));
    }

    public function testIsQuerySelect(): void
    {
        $this->assertTrue(StaticHelper::isQuerySelect('SELECT * FROM person;'));
        $this->assertFalse(StaticHelper::isQuerySelect('DELETE FROM person'));
        $this->assertFalse(StaticHelper::isQuerySelect('DROP TABLE person;'));
    }

    public function testRemoveAccents(): void
    {
        $this->assertEquals('a a a c e e e e i i o o u u u y A A A C E E E E I I O O U U U Y', StaticHelper::removeAccents('à â ä ç é è ê ë î ï ô ö ù û ü ÿ À Â Ä Ç É È Ê Ë Î Ï Ô Ö Ù Û Ü Ÿ'));
        $this->assertEquals(null, StaticHelper::removeAccents(null));
    }

    public function testSlugify(): void
    {
        $this->assertEquals('hello_there_how_nice_is_this', StaticHelper::slugify('héllo (there) / \ ||\'how" {%nice%} & *# is [this]?!'));
        $this->assertEquals('hello-world', StaticHelper::slugify('hello ()   wôrld', '-'));
    }

    public function testCamelCaseToSnakeCaseConverter(): void
    {
        $this->assertEquals('hello_world', StaticHelper::camelCaseToSnakeCaseConverter('HelloWorld'));
    }

    public function testSnakeCaseToCamelCaseToConverter(): void
    {
        $this->assertEquals('helloWorld', StaticHelper::snakeCaseToCamelCaseToConverter('hello_world'));
        $this->assertEquals('helloWorld', StaticHelper::snakeCaseToCamelCaseToConverter('hello_world', false));
        $this->assertEquals('HelloWorld', StaticHelper::snakeCaseToCamelCaseToConverter('hello_world', true));
    }

    public function testChance()
    {
        $this->assertEquals(1, StaticHelper::chance(100));
        $this->assertEquals(0, StaticHelper::chance(0));
    }

    public function testSanitizeString()
    {
        $stringToSanitize = 'Test with accentuated characters : éàéèï';
        $stringSanitized = StaticHelper::sanitizeString($stringToSanitize);
        $this->assertEquals('test-with-accentuated-characters-eaeei', $stringSanitized);
    }
}
