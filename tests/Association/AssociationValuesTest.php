<?php

namespace Association;

use Brzuchal\Saga\Association\AssociationValue;
use Brzuchal\Saga\Association\AssociationValues;
use PHPUnit\Framework\TestCase;

class AssociationValuesTest extends TestCase
{
    public function testCreateEmpty(): void
    {
        $associationValues = new AssociationValues([]);
        $this->assertCount(0, $associationValues);
        $this->assertEquals(0, $associationValues->count());
        $this->assertIsIterable($associationValues);
        $this->assertEmpty($associationValues->addedAssociations());
        $this->assertEmpty($associationValues->removedAssociations());
    }

    public function testCreateWithValue(): void
    {
        $associationValue = new AssociationValue('id', '1771d827-61ad-439c-b627-34ca6b0e8a98');
        $associationValues = new AssociationValues([$associationValue]);
        $this->assertCount(1, $associationValues);
        $this->assertEquals(1, $associationValues->count());
        $this->assertNotEmpty($associationValues);
        $this->assertEmpty($associationValues->addedAssociations());
        $this->assertEmpty($associationValues->removedAssociations());
    }

    public function testContains(): void
    {
        $associationValue = new AssociationValue('id', '1771d827-61ad-439c-b627-34ca6b0e8a98');
        $associationValues = new AssociationValues([$associationValue]);
        $this->assertTrue($associationValues->contains($associationValue));
    }

    public function testNotContains(): void
    {
        $associationValue = new AssociationValue('id', '1771d827-61ad-439c-b627-34ca6b0e8a98');
        $associationValues = new AssociationValues([$associationValue]);
        $this->assertFalse($associationValues->contains(new AssociationValue('id', '5f42b8e8-2969-4efb-8b47-1dec558e5702')));
    }

    public function testAddAndRemove(): void
    {
        $initialAssociationValue = new AssociationValue('id', '1771d827-61ad-439c-b627-34ca6b0e8a98');
        $associationValues = new AssociationValues([$initialAssociationValue]);
        $associationValue = new AssociationValue('id', '56e9b4d3-1042-48ac-a1e2-b77076610702');

        $associationValues->add($associationValue);
        $this->assertContains($associationValue, $associationValues);
        $this->assertContains($associationValue, $associationValues->addedAssociations());
        $this->assertNotContains($associationValue, $associationValues->removedAssociations());

        $associationValues->remove($initialAssociationValue);
        $this->assertContains($associationValue, $associationValues);
        $this->assertContains($associationValue, $associationValues->addedAssociations());
        $this->assertNotContains($associationValue, $associationValues->removedAssociations());
        $this->assertNotContains($initialAssociationValue, $associationValues);
        $this->assertNotContains($initialAssociationValue, $associationValues->addedAssociations());
        $this->assertContains($initialAssociationValue, $associationValues->removedAssociations());

        $associationValues->remove($associationValue);
        $this->assertNotContains($associationValue, $associationValues);
        $this->assertNotContains($associationValue, $associationValues->addedAssociations());
        $this->assertNotContains($associationValue, $associationValues->removedAssociations());
    }

    public function testRemove(): void
    {
        $initialAssociationValue1 = new AssociationValue('id', '1771d827-61ad-439c-b627-34ca6b0e8a98');
        $initialAssociationValue2 = new AssociationValue('id', '56e9b4d3-1042-48ac-a1e2-b77076610702');
        $associationValues = new AssociationValues([$initialAssociationValue1, $initialAssociationValue2]);

        $associationValues->remove($initialAssociationValue2);
        $this->assertNotContains($initialAssociationValue2, $associationValues);
        $this->assertNotContains($initialAssociationValue2, $associationValues->addedAssociations());
        $this->assertContains($initialAssociationValue2, $associationValues->removedAssociations());

        $associationValues->add($initialAssociationValue2);
        $this->assertContains($initialAssociationValue2, $associationValues);
        $this->assertNotContains($initialAssociationValue2, $associationValues->addedAssociations());
        $this->assertNotContains($initialAssociationValue2, $associationValues->removedAssociations());
    }
}
