<?php
/**
 * Copyright (C) 2015 David Young
 *
 * Tests the conditional query builder
 */
namespace RDev\QueryBuilders;

class ConditionalQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests adding a condition to an empty clause
     */
    public function testAddingConditionToEmptyClause()
    {
        $conditions = [];
        $queryBuilder = new ConditionalQueryBuilder();
        $conditions = $queryBuilder->addConditionToClause($conditions, "AND", "name = 'dave'");
        $this->assertEquals([["operation" => "AND", "condition" => "name = 'dave'"]], $conditions);
    }

    /**
     * Tests adding a condition to a non-empty clause
     */
    public function testAddingConditionToNonEmptyClause()
    {
        $conditions = [["operation" => "OR", "condition" => "email = 'foo@bar.com'"]];
        $queryBuilder = new ConditionalQueryBuilder();
        $conditions = $queryBuilder->addConditionToClause($conditions, "AND", "name = 'dave'");
        $this->assertEquals([
            ["operation" => "OR", "condition" => "email = 'foo@bar.com'"],
            ["operation" => "AND", "condition" => "name = 'dave'"]
        ], $conditions);
    }

    /**
     * Tests adding an "AND"ed "WHERE" statement
     */
    public function testAndWhere()
    {
        $queryBuilder = new ConditionalQueryBuilder();
        $queryBuilder->andWhere("name = 'dave'");
        $this->assertEquals([["operation" => "AND", "condition" => "name = 'dave'"]], $queryBuilder->getWhereConditions());
    }

    /**
     * Tests getting the SQL for a conditional clause
     */
    public function testGettingSQL()
    {
        $queryBuilder = new ConditionalQueryBuilder();
        $queryBuilder->where("name = 'dave'")
            ->orWhere("email = 'foo@bar.com'")
            ->andWhere("awesome = true");
        $this->assertEquals(" WHERE (name = 'dave') OR (email = 'foo@bar.com') AND (awesome = true)",
            $queryBuilder->getClauseConditionSQL("WHERE", $queryBuilder->getWhereConditions()));
    }

    /**
     * Tests adding an "OR"ed "WHERE" statement
     */
    public function testOrWhere()
    {
        $queryBuilder = new ConditionalQueryBuilder();
        $queryBuilder->orWhere("name = 'dave'");
        $this->assertEquals([["operation" => "OR", "condition" => "name = 'dave'"]], $queryBuilder->getWhereConditions());
    }

    /**
     * Tests adding "WHERE" statement
     */
    public function testWhere()
    {
        $queryBuilder = new ConditionalQueryBuilder();
        $queryBuilder->where("name = 'dave'");
        $this->assertEquals([["operation" => "AND", "condition" => "name = 'dave'"]],
            $queryBuilder->getWhereConditions());
    }
} 