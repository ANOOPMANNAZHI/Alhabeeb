<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

class TerminationDuesCategoryTest extends TestCase
{
    public function test_default_map_classifies_the_known_account_codes()
    {
        $map = Cat::defaultAccountMap();

        $this->assertSame(Cat::RENT, Cat::fromAccountCode('12211', $map));
        $this->assertSame(Cat::MUNICIPAL, Cat::fromAccountCode('41102', $map));
        $this->assertSame(Cat::MUNICIPAL, Cat::fromAccountCode('41103', $map));
        $this->assertSame(Cat::EW, Cat::fromAccountCode('41105', $map));
        $this->assertSame(Cat::EW, Cat::fromAccountCode('22305', $map));
        $this->assertSame(Cat::MAINTENANCE, Cat::fromAccountCode('41110', $map));
        $this->assertSame(Cat::MAINTENANCE, Cat::fromAccountCode('12302', $map));
    }

    public function test_unknown_or_empty_code_is_unclassified()
    {
        $map = Cat::defaultAccountMap();

        $this->assertNull(Cat::fromAccountCode('22301', $map)); // cash in transit
        $this->assertNull(Cat::fromAccountCode('', $map));
        $this->assertNull(Cat::fromAccountCode(null, $map));
    }

    public function test_codes_are_compared_as_trimmed_strings()
    {
        $map = Cat::defaultAccountMap();
        $this->assertSame(Cat::RENT, Cat::fromAccountCode(12211, $map));
        $this->assertSame(Cat::RENT, Cat::fromAccountCode(' 12211 ', $map));
    }

    public function test_owner_team_per_category()
    {
        $this->assertSame(Cat::TEAM_MAINTENANCE, Cat::ownerFor(Cat::MAINTENANCE));
        foreach ([Cat::RENT, Cat::MUNICIPAL, Cat::EW, Cat::OTHER] as $c) {
            $this->assertSame(Cat::TEAM_BACKOFFICE, Cat::ownerFor($c));
        }
    }

    public function test_parse_map_falls_back_to_default_on_bad_json()
    {
        $this->assertSame(Cat::defaultAccountMap(), Cat::parseMap(''));
        $this->assertSame(Cat::defaultAccountMap(), Cat::parseMap('not json'));

        $custom = Cat::parseMap('{"rent":["99999"],"ew":["41105"]}');
        $this->assertSame(Cat::RENT, Cat::fromAccountCode('99999', $custom));
        $this->assertNull(Cat::fromAccountCode('12211', $custom));
    }

    public function test_labels_exist_for_every_category()
    {
        foreach (Cat::all() as $c) {
            $this->assertNotSame('', Cat::label($c));
        }
        $this->assertSame('Electricity & water', Cat::label(Cat::EW));
    }
}
