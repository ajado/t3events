<?php
namespace DWenzel\T3events\Tests\Unit\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *  (c) 2015 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Test case for class \DWenzel\T3events\Domain\Repository\AbstractDemandedRepository.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package TYPO3
 * @subpackage Events
 * @author Dirk Wenzel <dirk.wenzel@cps-it.de>
 * @coversDefaultClass \DWenzel\T3events\Domain\Repository\AbstractDemandedRepository
 */
class AbstractDemandedRepositoryTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{

    /**
     * @var \DWenzel\T3events\Domain\Repository\AbstractDemandedRepository
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createConstraintsFromDemand', 'createQuery'), array(), '', false);
    }

    /**
     * @test
     * @covers ::createOrderingsFromDemand
     */
    public function createOrderingsFromDemandReturnsInitiallyEmptyArray()
    {
        $expectedResult = array();
        $demand = $this->getMockForAbstractClass(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand'
        );
        $this->assertEquals(
            $expectedResult,
            $this->fixture->_call('createOrderingsFromDemand', $demand)
        );
    }

    /**
     * @test
     * @covers ::createOrderingsFromDemand
     */
    public function createOrderingsFromDemandReturnsEmptyArrayForEmptyOrderList()
    {
        $expectedResult = array();
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array('getOrder'), array(), '', false
        );
        $emptyOrderList = '';
        $mockDemand->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($emptyOrderList));

        $this->assertEquals(
            $expectedResult,
            $this->fixture->_call('createOrderingsFromDemand', $mockDemand)
        );
    }

    /**
     * @test
     * @covers ::createOrderingsFromDemand
     */
    public function createOrderingsFromDemandReturnsOrderingsForFieldWithoutOrder()
    {
        $fieldName = 'foo';
        $expectedResult = array(
            $fieldName => QueryInterface::ORDER_ASCENDING
        );
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array('getOrder'), array(), '', false
        );

        $mockDemand->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($fieldName));

        $this->assertEquals(
            $expectedResult,
            $this->fixture->_call('createOrderingsFromDemand', $mockDemand)
        );
    }

    /**
     * @test
     * @covers ::createOrderingsFromDemand
     */
    public function createOrderingsFromDemandReturnsOrderingsForFieldWithDescendingOrder()
    {
        $fieldWithDescendingOrder = 'foo|desc';
        $expectedResult = array(
            'foo' => QueryInterface::ORDER_DESCENDING
        );
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array('getOrder'), array(), '', false
        );

        $mockDemand->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($fieldWithDescendingOrder));

        $this->assertEquals(
            $expectedResult,
            $this->fixture->_call('createOrderingsFromDemand', $mockDemand)
        );
    }

    /**
     * @test
     * @covers ::createOrderingsFromDemand
     */
    public function createOrderingsFromDemandReturnsOrderingsForMultipleFieldsWithDifferentOrder()
    {
        $fieldsWithDifferentOrder = 'foo|desc,bar|asc';
        $expectedResult = array(
            'foo' => QueryInterface::ORDER_DESCENDING,
            'bar' => QueryInterface::ORDER_ASCENDING
        );
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array('getOrder'), array(), '', false
        );

        $mockDemand->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($fieldsWithDifferentOrder));

        $this->assertEquals(
            $expectedResult,
            $this->fixture->_call('createOrderingsFromDemand', $mockDemand)
        );
    }

    /**
     * @test
     * @covers ::findDemanded
     */
    public function findDemandedGeneratesAndExecutesQuery()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createConstraintsFromDemand', 'generateQuery'), array(), '', false);
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array(), array(), '', false
        );
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('execute'), array(), '', false
        );
        $expectedResult = 'foo';
        $respectEnableFields = false;

        $fixture->expects($this->once())
            ->method('generateQuery')
            ->with($mockDemand, $respectEnableFields)
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($expectedResult));

        $this->assertEquals(
            $expectedResult,
            $fixture->findDemanded($mockDemand, $respectEnableFields)
        );
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQueryCreatesQueryAndConstraints()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createConstraintsFromDemand', 'createQuery'), array(), '', false);
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array(), array(), '', false
        );
        $mockQuery = $this->getMockForAbstractClass(
            'TYPO3\\CMS\\Extbase\\Persistence\\QueryInterface'
        );

        $fixture->expects($this->once())
            ->method('createQuery')
            ->with()
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand')
            ->with($mockQuery, $mockDemand)
            ->will($this->returnValue(array()));

        $this->assertSame(
            $mockQuery,
            $fixture->_call('generateQuery', $mockDemand)
        );
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQueryReturnsQueryMatchingConstraints()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createConstraintsFromDemand', 'createQuery'), array(), '', false);
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array(), array(), '', false
        );
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('matching', 'logicalAnd'), array(), '', false
        );
        $mockConstraints = array('foo');

        $fixture->expects($this->once())
            ->method('createQuery')
            ->with()
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand')
            ->with($mockQuery, $mockDemand)
            ->will($this->returnValue($mockConstraints));
        $mockQuery->expects($this->once())
            ->method('matching')
            ->with($mockQuery);
        $mockQuery->expects($this->once())
            ->method('logicalAnd')
            ->with($mockConstraints)
            ->will($this->returnValue($mockQuery));

        $fixture->_call('generateQuery', $mockDemand);
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQuerySetsOrderings()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createQuery', 'createConstraintsFromDemand', 'createOrderingsFromDemand'), array(), '', false);
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array(), array(), '', false
        );
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('setOrderings'), array(), '', false
        );
        $mockOrderings = array('foo' => 'bar');

        $fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand');
        $fixture->expects($this->once())
            ->method('createOrderingsFromDemand')
            ->will($this->returnValue($mockOrderings));
        $mockQuery->expects($this->once())
            ->method('setOrderings')
            ->with($mockOrderings);
        $fixture->_call('generateQuery', $mockDemand);
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQuerySetsIgnoreEnableFields()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createQuery', 'createConstraintsFromDemand', 'createOrderingsFromDemand'), array(), '', false);
        $mockDemand = $this->getMock(
            'DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand',
            array(), array(), '', false
        );
        $mockQuerySettings = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('setOrderings', 'getQuerySettings'), array(), '', false
        );

        $fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand');
        $fixture->expects($this->once())
            ->method('createOrderingsFromDemand');
        $mockQuery->expects($this->once())
            ->method('getQuerySettings')
            ->will($this->returnValue($mockQuerySettings));
        $mockQuerySettings->expects($this->once())
            ->method('setIgnoreEnableFields')
            ->with(true);

        $fixture->_call('generateQuery', $mockDemand, false);
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQuerySetsLimitFromDemand()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createQuery', 'createConstraintsFromDemand'), array(), '', false);
        $mockDemand = $this->getAccessibleMockForAbstractClass('DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand');
        $limit = 3;
        $mockDemand->setLimit($limit);
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('setLimit'), array(), '', false
        );
        $fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand');

        $mockQuery->expects($this->once())
            ->method('setLimit')
            ->with($limit);
        $fixture->_call('generateQuery', $mockDemand);
    }

    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQuerySetsOffsetFromDemand()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createQuery', 'createConstraintsFromDemand'), array(), '', false);
        $mockDemand = $this->getAccessibleMockForAbstractClass('DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand');
        $offset = 3;
        $mockDemand->setOffset($offset);
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('setOffset'), array(), '', false
        );
        $fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $fixture->expects($this->once())
            ->method('createConstraintsFromDemand');

        $mockQuery->expects($this->once())
            ->method('setOffset')
            ->with($offset);
        $fixture->_call('generateQuery', $mockDemand);
    }


    /**
     * @test
     * @covers ::generateQuery
     */
    public function generateQuerySetsStoragePageIdsFromDemand()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('createQuery', 'createConstraintsFromDemand'), array(), '', false);
        $mockDemand = $this->getAccessibleMockForAbstractClass('DWenzel\\T3events\\Domain\\Model\\Dto\\AbstractDemand');
        $storagePageIds = '3,5';
        $mockDemand->setStoragePages($storagePageIds);
        $mockDemand->setOffset($storagePageIds);
        $mockQuery = $this->getAccessibleMockForAbstractClass(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array(), '', false);
        $mockQuerySettings = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
        $mockQuery->_set('querySettings', $mockQuerySettings);
        $fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));

        $expectedStoragePageIds = GeneralUtility::intExplode(',', $storagePageIds);

        $mockQuerySettings->expects($this->once())
            ->method('setStoragePageIds')
            ->with($expectedStoragePageIds);
        $fixture->_call('generateQuery', $mockDemand);
    }

    /**
     * @test
     * @covers ::combineConstraints
     */
    public function combineConstraintsInitiallyCombinesLogicalAnd()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('dummy', 'createConstraintsFromDemand'), array(), '', false);
        $constraints = array();
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('logicalAnd'), array(), '', false);
        $additionalConstraint = array(
            $this->getMock(
                'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Qom\\ConstraintInterface'
            )
        );

        $mockQuery->expects($this->once())
            ->method('logicalAnd')
            ->with($additionalConstraint);
        $fixture->_callRef(
            'combineConstraints',
            $mockQuery,
            $constraints,
            $additionalConstraint
        );
    }

    /**
     * @test
     * @covers ::combineConstraints
     */
    public function combineConstraintsCombinesLogicalOr()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('dummy', 'createConstraintsFromDemand'), array(), '', false);
        $constraints = array();
        $conjunction = 'or';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('logicalOr'), array(), '', false);
        $additionalConstraint = array(
            $this->getMock(
                'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Qom\\ConstraintInterface'
            )
        );

        $mockQuery->expects($this->once())
            ->method('logicalOr')
            ->with($additionalConstraint);
        $fixture->_callRef(
            'combineConstraints',
            $mockQuery,
            $constraints,
            $additionalConstraint,
            $conjunction
        );
    }

    /**
     * @test
     * @covers ::combineConstraints
     */
    public function combineConstraintsCombinesLogicalNotAnd()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('dummy', 'createConstraintsFromDemand'), array(), '', false);
        $constraints = array();
        $conjunction = 'NotAnd';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('logicalNot', 'logicalAnd'), array(), '', false);
        $mockConstraint = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Qom\\ConstraintInterface'
        );
        $additionalConstraint = array(
            $mockConstraint
        );

        $mockQuery->expects($this->once())
            ->method('logicalAnd')
            ->with($mockConstraint)
            ->will($this->returnValue($mockConstraint));
        $mockQuery->expects($this->once())
            ->method('logicalNot')
            ->with($mockConstraint);
        $fixture->_callRef(
            'combineConstraints',
            $mockQuery,
            $constraints,
            $additionalConstraint,
            $conjunction
        );
    }

    /**
     * @test
     * @covers ::combineConstraints
     */
    public function combineConstraintsCombinesLogicalNotOr()
    {
        $fixture = $this->getAccessibleMock(
            'DWenzel\\T3events\\Domain\\Repository\\AbstractDemandedRepository',
            array('dummy', 'createConstraintsFromDemand'), array(), '', false);
        $constraints = array();
        $conjunction = 'NotOr';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query',
            array('logicalNot', 'logicalOr'), array(), '', false);
        $mockConstraint = $this->getMock(
            'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Qom\\ConstraintInterface'
        );
        $additionalConstraint = array(
            $mockConstraint
        );

        $mockQuery->expects($this->once())
            ->method('logicalOr')
            ->with($mockConstraint)
            ->will($this->returnValue($mockConstraint));
        $mockQuery->expects($this->once())
            ->method('logicalNot')
            ->with($mockConstraint);
        $fixture->_callRef(
            'combineConstraints',
            $mockQuery,
            $constraints,
            $additionalConstraint,
            $conjunction
        );
    }

    /**
     * @test
     */
    public function findMultipleByUidReturnsQuery()
    {
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(Query::class, [], [], '', false);
        $mockResult = $this->getMock(QueryResultInterface::class);
        $mockQuery->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $this->fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));

        $this->assertSame(
            $mockResult,
            $this->fixture->findMultipleByUid(
                '1,2', null
            )
        );
    }

    /**
     * @test
     */
    public function findMultipleByUidMatchesUidList()
    {
        $uidList = '1,2';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(Query::class, [], [], '', false);
        $mockQuery->expects($this->once())
            ->method('matching')
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->once())
            ->method('in')
            ->with('uid', [1,2])
            ->will($this->returnValue($mockQuery));

        $this->fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));

        $this->fixture->findMultipleByUid($uidList, null);
    }

    /**
     * @test
     */
    public function findMultipleByUidSetsDefaultOrderings()
    {
        $uidList = '';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(Query::class, [], [], '', false);

        $this->fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->once())
            ->method('setOrderings')
            ->with(['uid' => QueryInterface::ORDER_ASCENDING]);

        $this->fixture->findMultipleByUid($uidList);
    }

    /**
     * @test
     */
    public function findMultipleByUidSetsOrderings()
    {
        $sortField = 'foo';
        $order = QueryInterface::ORDER_DESCENDING;

        $uidList = '';
        /** @var QueryInterface $mockQuery */
        $mockQuery = $this->getMock(Query::class, [], [], '', false);

        $this->fixture->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->once())
            ->method('setOrderings')
            ->with([$sortField => QueryInterface::ORDER_DESCENDING]);

        $this->fixture->findMultipleByUid($uidList, $sortField, $order);
    }
}
