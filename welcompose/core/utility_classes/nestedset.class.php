<?php

/**
 * Project: Welcompose
 * File: nestedset.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

define("UTILITY_NESTEDSET_CREATE_BEFORE", 2001);
define("UTILITY_NESTEDSET_CREATE_AFTER", 2002);


/**
 * Singleton. Returns instance of the Utility_NestedSet object.
 * 
 * @return object
 */
function Utility_NestedSet ()
{ 
	if (Utility_NestedSet::$instance == null) {
		Utility_NestedSet::$instance = new Utility_NestedSet(); 
	}
	return Utility_NestedSet::$instance;
}

class Utility_NestedSet {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Wrapper around all the other create* functions. Decides which of all the
 * different create* functions to use.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @param int Position information
 * @return int Insert id
 */
public function createNode ($navigation, $reference = null, $position = null)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (!empty($reference) && !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for parameter reference is expected to be numeric");
	}
	if (!empty($position) && !is_numeric($position)) {
		throw new Utility_NestedSetException("Input for parameter position is expected to be numeric");
	}
	
	// if there's no reference node, we have to create a root node
	if (empty($reference)) {
		return $this->createRootNode($navigation);
	}
		
	// now, the reference node can only be a normal node. so we've to look
	// at $position to decide which create* function to use
	switch ($position) {
		case UTILITY_NESTEDSET_CREATE_BEFORE:
				return $this->createNodeAbove($navigation, $reference, $position);
			break;
		case UTILITY_NESTEDSET_CREATE_AFTER:
				return $this->createNodeBelow($navigation, $reference, $position);
			break;
		default:
			throw new Utility_NestedSetException("Unknown position supplied");
	}
}

/**
 * Wrapper around the different delete* functions. Decides which of all the
 * different delete functions to use.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return int Amount of affected rows
 */
public function deleteNode ($navigation, $node)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node) || !is_numeric($node)) {
		throw new Utility_NestedSetException("Input for parameter node is expected to be numeric");
	}
	
	// let's see if it's a root node
	if ($this->root_node($node, $navigation)) {
		return $this->deleteRootNode($navigation, $node);
	} else {
		return $this->deleteNodeInTree($navigation, $node);
	}
}

/**
 * Wrapper around the different move* functions. Deciedes which of all the
 * different move functions to use.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @param string Move direction
 * @return bool
 */
public function moveNode ($navigation, $node, $direction = "down")
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is not numeric");
	}
	if (empty($node) || !is_numeric($node)) {
		throw new Utility_NestedSetException("Input for parameter node is not numeric");
	}
	if ($direction !== 'down' && $direction !== 'up') {
		throw new Utility_NestedSetException("Move direction can either be up or down");
	}
	
	switch ((string)$direction) {
		case 'up':
				return $this->moveUpInTree($navigation, $node);
			break;
		case 'down':
				return $this->moveDownInTree($navigation, $node);
			break;
	}
}

/**
 * Creates new root node. Takes the navigation id as first argument, the
 * id of the reference node after or before the new node will be created
 * as second argument and the positioning information as third argument.
 * Returns insert id of the new node.
 * 
 * Supported constants for parameter position:
 * 
 * <ul>
 * <li>UTILITY_NESTEDSET_CREATE_BEFORE: node will be created before
 * reference node</li>
 * <li>UTILITY_NESTEDSET_CREATE_AFTER: node will be created after
 * reference node</li>
 * </ul>
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @param int Position information
 * @return int Insert id
 */
public function createRootNode ($navigation, $reference = null, $position = null)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (!empty($reference) && !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for parameter reference is expected to be numeric");
	}
	if (!empty($position) && !is_numeric($position)) {
		throw new Utility_NestedSetException("Input for parameter position is expected to be numeric");
	}
	
	// define sorting
	$sorting = 0;
	
	// if the reference node exists, look at the position constant and place the
	// new node according to it.
	if (!empty($reference) && $this->node_exists($reference)) {
		// get reference node
		$result = $this->selectNode($reference);
		
		switch ($position) {
			case UTILITY_NESTEDSET_CREATE_BEFORE:
					// adjust sorting of the reference node and the following nodes
					$sql = "
						UPDATE
							`".WCOM_DB_CONTENT_NODES."`
						SET
							`sorting` = `sorting` + 1
						WHERE
							`sorting` >= :old_sorting
						AND
							`navigation` = :navigation
					";
					
					// prepare bind params
					$bind_params = array(
						'old_sorting' => Base_Cnc::ifsetor($result['sorting'], 0),
						'navigation' => $navigation
					);
					
					// execute query
					$this->base->db->execute($sql, $bind_params);
					
					// set sorting
					$sorting = $result['sorting'];
				break;
			case UTILITY_NESTEDSET_CREATE_AFTER:
					// adjust sorting of the reference node and the following nodes
					$sql = "
						UPDATE
							`".WCOM_DB_CONTENT_NODES."`
						SET
							`sorting` = `sorting` + 1
						WHERE
							`sorting` > :old_sorting
						AND
							`navigation` = :navigation
					";
					
					// prepare bind params
					$bind_params = array(
						'old_sorting' => Base_Cnc::ifsetor($result['sorting'], 0),
						'navigation' => $navigation
					);
					
					// execute query
					$this->base->db->execute($sql, $bind_params);
					
					// set sorting
					$sorting = Base_Cnc::ifsetor($result['sorting'], 0) + 1;
				break;
			default:
				throw new Utility_NestedSetException("Unknown position constant supplied");
		}
	} else {
		// if we can't find the reference node, simply append the
		// node at the end of the navigation
		$sorting = $this->selectMaxSorting($navigation) + 1;
	}
	
	// prepare sql data
	$sqlData = array(
		'navigation' => $navigation,
		'parent' => null,
		'lft' => 1,
		'rgt' => 2,
		'level' => 1,
		'sorting' => (int)$sorting
	);
	
	// insert node
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_NODES, $sqlData);
	
	// adjust root node
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`root_node` = `id`
		WHERE 
			`id` = :id
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $insert_id
	);
	
	// update node
	$this->base->db->execute($sql, $bind_params);
	
	// return insert id
	return $insert_id;
}

/**
 * Creates node above existing node. Takes the navigation id as first
 * argument, the id of the reference node above that the new node will
 * be positioned as second argument. Returns insert id.
 *
 * @throws
 * @param int Navigation id
 * @param int Reference node id
 * @return int Insert id
 */
public function createNodeAbove ($navigation, $reference)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for parameter reference is expected to be numeric");
	}
	
	// make sure that the reference node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Reference node does not exist");
	}
	
	// get reference node
	$reference_node = $this->selectNode($reference);
	
	// if the reference node is a root node, we need to use the special
	// method for root node creation
	if ($reference_node['lft'] == 1) {
		return 	$this->createRootNode ($navigation, $reference, UTILITY_NESTEDSET_CREATE_BEFORE);
	}
	
	// update lft of future siblings
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` + 2
		WHERE
			`root_node` = :root_node
		AND
			`lft` >= :lft
	";

	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$reference_node['root_node'],
		'lft' => (int)$reference_node['lft'] 
	);

	// execute query
	$this->base->db->execute($sql, $bind_params);

	// update rgt of future siblings
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`rgt` = `rgt` + 2
		WHERE
			`root_node` = :root_node
		AND
			`rgt` >= :lft
	";

	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$reference_node['root_node'],
		'lft' => (int)$reference_node['lft']
	);

	// execute query
	$this->base->db->execute($sql, $bind_params);

	// prepare sql data
	$sqlData = array(
		'navigation' => (int)$navigation,
		'root_node' => (int)$reference_node['root_node'],
		'parent' => (int)$reference_node['id'],
		'lft' => (int)$reference_node['lft'],
		'rgt' => (int)$reference_node['lft'] + 1,
		'level' => (int)$reference_node['level'],
		'sorting' => (int)$reference_node['sorting']
	);

	// insert node
	return $this->base->db->insert(WCOM_DB_CONTENT_NODES, $sqlData);
}


/**
 * Creates node below existing node (~ creates new branch). Takes the
 * navigation id as first argument, the id of the reference node where
 * the sub node will be attached to as second argument. Returns insert id.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @return int Insert id
 */
public function createNodeBelow ($navigation, $reference)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for parameter reference is expected to be numeric");
	}
	
	// make sure that the reference node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Reference node does not exist");
	}
	
	// get reference node
	$reference_node = $this->selectNode($reference);
	
	// update lft of future siblings
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` + 2
		WHERE
			`root_node` = :root_node
		AND
			`lft` > :lft
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$reference_node['root_node'],
		'lft' => (int)$reference_node['lft'] 
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// update rgt of future siblings
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`rgt` = `rgt` + 2
		WHERE
			`root_node` = :root_node
		AND
			`rgt` >= :lft
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$reference_node['root_node'],
		'lft' => (int)$reference_node['lft']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// prepare sql data
	$sqlData = array(
		'navigation' => (int)$navigation,
		'root_node' => (int)$reference_node['root_node'],
		'parent' => (int)$reference_node['id'],
		'lft' => (int)$reference_node['lft'] + 1 ,
		'rgt' => (int)$reference_node['lft'] + 2,
		'level' => (int)$reference_node['level'] + 1,
		'sorting' => (int)$reference_node['sorting']
	);
	
	// insert node
	return $this->base->db->insert(WCOM_DB_CONTENT_NODES, $sqlData);
}

/**
 * Moves node up across two trees. Takes the navigation id as first
 * argument, the id of the node to move as second argument. Returns
 * boolean true.
 * 
 * Attention: Is only able to move nodes with a lft between 1 and 2.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation Id
 * @param int Node id
 * @return bool
 */
public function moveUpAcrossTrees ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that we don't try to move a normal node
	if ((int)$node['lft'] > 2) {
		return $this->moveUpInTree($navigation, $node_id);
	}
	
	// get sibling above and the sibling above that's one level deeper. we need them
	// both 'cos of the following case:
	// 
	// Test 1
	// - Test 2
	// - - Test 3
	// Test 4 <---
	//
	// let's imaginge we're going to move 'Test 4'. in our model we're expecting that
	// 'Test 4' will be turned into a child of 'Test 1'. But selectSiblingAbove() will
	// return 'Test 3' as new reference point, so that 'Test 4' would be a child of
	// 'Test 2' -- and that's not what we want. siblingAboveOneLevelDeeper() returns
	// 'Test 2', so that we can turn 'Test 4' into a child of 'Test 1'.	
	$sibling_above_one_level_deeper = $this->selectSiblingAboveOneLevelDeeper($navigation, $node['id']);
	$sibling_above = $this->selectSiblingAbove($navigation, $node['id']);
	
	// if there's no sibling above that's one level deeper, take the sibling above
	// as an alias so that our rules below still work.
	if (empty($sibling_above_one_level_deeper)) {
		$sibling_above_one_level_deeper = $sibling_above;
	}
	
	// get min/max sorting
	$max_sorting = $this->selectMaxSorting($navigation);
	$min_sorting = $this->selectMinSorting($navigation);
	
	// get sibling below 
	$sibling_below = $this->selectSiblingBelow($navigation, $node['id']);
	
	// Handles move if the current node is the only node in the navigation.
	//
	// Test 1 <---
	//
	if ($node['lft'] == 1 && $node['rgt'] == 2 && $node['sorting'] == $min_sorting && $node['sorting'] == $max_sorting) {
		// nothing has to be done here
		return true;
	
	// handles move if the current node is top-most node in the navigation and it
	// has no childs
	//
	// Test 1  <---
	// Test 2
	// 
	// We have move 'Test 1' at the end of the navigation:
	// 
	// Test 2
	// Test 1  <---
	//
	} elseif ($node['lft'] == 1 && $node['sorting'] == $min_sorting && ($node['rgt'] - $node['lft']) == 1) {
		// adjust sorting
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`sorting` > :sorting
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// now, we have to turn the sibling below the current
		// node into a real root node.
		$sqlData = array(
			'sorting' => (int)$this->selectMaxSorting($navigation) + 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
	// handles move if the current node is top-most node in the navigation.
	//
	// Test 1  <---
	// - Test 2
	// - - Test 3
	// - Test 4
	// Test 5
	// 
	// We have move 'Test 1' at the end of the navigation:
	// 
	// Test 2
	// - Test 3
	// - Test 4
	// Test 5
	// Test 1  <---
	//	
	} elseif ($node['lft'] == 1 && $node['sorting'] == $min_sorting && ($node['rgt'] - $node['lft']) > 1) {
		// the next step is to swap the parent ids and root node ids
		// of the current node with them of the sibling below. 
		// let's start with the root node ids.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_root_node' => (int)$node['id'],
			'new_root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// now the parent ids
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_parent' => (int)$node['id'],
			'new_parent' => (int)$sibling_below['id'],
			'root_node' => (int)$sibling_below['root_node'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// so, now we have to fix the old tree. our first task is to fix the subtree
		// of our new root node. let's start with the lfts, rgts and the level.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1,
				`level` = `level` - 1
			WHERE
				`lft` > :lft
			AND
				`rgt` < :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// the next task is to fix the rest of the tree. we should be done
		// with lft and rgt adjustments.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 2,
				`rgt` = `rgt` - 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// now, we have to turn the sibling below the current
		// node into a real root node.
		$sqlData = array(
			'root_node' => $sibling_below['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => (int)$node['rgt'] - 2,
			'level' => 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);		
		
		// now, the last task is to take the node out of the tree and to
		// place it at the end of the navigation.
		$sqlData = array(
			'root_node' => $node['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => 2,
			'level' => 1,
			'sorting' => (int)$this->selectMaxSorting($navigation) + 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
	// Handles move if node is a root node (but not the top-most root node) and the
	// sibling above has no childs and the sibling below is a root node
	//
	// Test 1
	// Test 2 <---
	// Test 3
	// 
	// We have turn 'Test 2' into a child of 'Test 1':
	// 
	// Test 1
	// - Test 2 <---
	// Test 3
	//
	} elseif ($node['lft'] == 1  && $node['sorting'] != $min_sorting && $sibling_above['lft'] == 1 &&
	($node['rgt'] - $node['lft']) == 1) {
		// now, the first task is to take the node out of the tree and to
		// place it at the end of the tree above. first, we have to adjust
		// the lfts/rgts. let's start with the lfts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above['rgt'],
			'root_node' => (int)$sibling_above['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust rgts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`rgt` >= :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above['rgt'],
			'root_node' => (int)$sibling_above['root_node'],
			'navigation' => (int)$navigation
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);

		// take the node out of the current tree and put it in the tree above
		$sqlData = array(
			'root_node' => $sibling_above['root_node'],
			'parent' => $sibling_above['id'],
			'lft' => $sibling_above['rgt'],
			'rgt' => $sibling_above['rgt'] + 1,
			'level' => $sibling_above['level'] + 1,
			'sorting' => $sibling_above['sorting']
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// the last task is to close the sorting gap
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`sorting` > :sorting
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
	// Handles move if node is a root node (but not the top-most root node) and the
	// sibling above has no childs
	//
	// Test 1
	// Test 2 <---
	// - Test 3
	// - - Test 4
	// - Test 5
	// Test 6
	// 
	// We have turn 'Test 2' into a child of 'Test 1':
	// 
	// Test 1
	// - Test 2 <---
	// Test 3
	// - Test 4  
	// - Test 5
	// Test 6
	//
	} elseif ($node['lft'] == 1  && $node['sorting'] != $min_sorting && $sibling_above['lft'] == 1 &&
	($node['rgt'] - $node['lft']) > 1) {
		// the next step is to swap the parent ids and root node ids
		// of the current node with them of the sibling below. 
		// let's start with the root node ids.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_root_node' => (int)$node['id'],
			'new_root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// now the parent ids
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_parent' => (int)$node['id'],
			'new_parent' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// so, now we have to fix the old tree. our first task is to fix the subtree
		// of our new root node. let's start with the lfts, rgts and the level.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1,
				`level` = `level` - 1
			WHERE
				`lft` > :lft
			AND
				`rgt` < :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// the next task is to fix the rest of the tree. we should be done
		// with lft and rgt adjustments.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 2,
				`rgt` = `rgt` - 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// now, we have to turn the sibling below the current
		// node into a real root node.
		$sqlData = array(
			'root_node' => $sibling_below['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => (int)$node['rgt'] - 2,
			'level' => 1
		);
	
		// prepare where clause
		$where = " WHERE `id` = :id ";
	
		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);
	
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// now, the last task is to take the node out of the tree and to
		// place it at the end of the tree above. first, we have to adjust
		// the lfts/rgts. let's start with the lfts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust rgts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`rgt` >= :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// take the node out of the current tree and put it in the tree above
		$sqlData = array(
			'root_node' => $sibling_above_one_level_deeper['root_node'],
			'parent' => $sibling_above_one_level_deeper['id'],
			'lft' => $sibling_above_one_level_deeper['rgt'],
			'rgt' => $sibling_above_one_level_deeper['rgt'] + 1,
			'level' => $sibling_above_one_level_deeper['level'] + 1,
			'sorting' => $sibling_above_one_level_deeper['sorting']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

	// handles move if the current node is a root node (not the top-most) and the
	// sibling above has one or more childs.
	//
	// Test 1
	// - Test 2
	// - - Test 3
	// Test 4 <---
	// - Test 5
	// - - Test 6
	// - Test 7
	// Test 8
	// 
	// We have turn 'Test 2' into a child of 'Test 1':
	// 
	// Test 1
	// - Test 2
	// - - Test 3
	// - Test 4  <---
	// Test 5
	// - Test 6
	// - Test 7
	// Test 8
	//
	} elseif ($node['lft'] == 1  && $node['sorting'] != $min_sorting &&
	$sibling_above['lft'] > 1 && $sibling_below['lft'] > 1) {
		// the next step is to swap the parent ids and root node ids
		// of the current node with them of the sibling below. 
		// let's start with the root node ids.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_root_node' => (int)$node['id'],
			'new_root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// now the parent ids
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'old_parent' => (int)$node['id'],
			'new_parent' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// so, now we have to fix the old tree. our first task is to fix the subtree
		// of our new root node. let's start with the lfts, rgts and the level.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1,
				`level` = `level` - 1
			WHERE
				`lft` > :lft
			AND
				`rgt` < :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// the next task is to fix the rest of the tree. we should be done
		// with lft and rgt adjustments.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 2,
				`rgt` = `rgt` - 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// now, we have to turn the sibling below the current
		// node into a real root node.
		$sqlData = array(
			'root_node' => $sibling_below['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => (int)$node['rgt'] - 2,
			'level' => 1
		);
	
		// prepare where clause
		$where = " WHERE `id` = :id ";
	
		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);
	
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// now, the last task is to take the node out of the tree and to
		// place it at the end of the tree above. first, we have to adjust
		// the lfts/rgts. let's start with the lfts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust rgts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`rgt` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// take the node out of the current tree and put it in the tree above
		$sqlData = array(
			'root_node' => $sibling_above_one_level_deeper['root_node'],
			'parent' => $sibling_above_one_level_deeper['parent'],
			'lft' => $sibling_above_one_level_deeper['rgt'] + 1,
			'rgt' => $sibling_above_one_level_deeper['rgt'] + 2,
			'level' => $sibling_above_one_level_deeper['level'],
			'sorting' => $sibling_above_one_level_deeper['sorting']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
	// handles move if the current node is a root node (not the top-most) and the
	// sibling above has one or more childs.
	//
	// Test 1
	// - Test 2
	// - - Test 3
	// Test 4 <---
	// Test 5
	// 
	// We have turn 'Test 2' into a child of 'Test 1':
	// 
	// Test 1
	// - Test 2
	// - - Test 3
	// - Test 4  <---
	// Test 5
	//
	} elseif ($node['lft'] == 1  && $node['sorting'] != $min_sorting &&
	$sibling_above['lft'] > 1 && ($sibling_below['lft'] == 1 || empty($sibling_below))) {
		// the first task is to take the node out of the tree and to
		// place it at the end of the tree above. first, we have to adjust
		// the lfts/rgts. let's start with the lfts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`lft` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['id'],
			'navigation' => (int)$navigation
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);

		// adjust rgts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`rgt` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$sibling_above_one_level_deeper['rgt'],
			'root_node' => (int)$sibling_above_one_level_deeper['root_node'],
			'navigation' => (int)$navigation
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);

		// take the node out of the current tree and put it in the tree above
		$sqlData = array(
			'root_node' => $sibling_above_one_level_deeper['root_node'],
			'parent' => $sibling_above_one_level_deeper['parent'],
			'lft' => $sibling_above_one_level_deeper['rgt'] + 1,
			'rgt' => $sibling_above_one_level_deeper['rgt'] + 2,
			'level' => $sibling_above_one_level_deeper['level'],
			'sorting' => $sibling_above_one_level_deeper['sorting']
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

		// fix the sorting of the node below
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`sorting` > :sorting
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);
				
	// handles move if the current node is the only	node in its tree and
	// the next node above is a root node too.
	//
	// Test 1
	// - Test 2 <---
	// - - Test 3
	// - Test 4
	// Test 5
	// 
	// We have turn 'Test 2' into a new root node above 'Test 1':
	// 
	// Test 2 <---
	// Test 1
	// - Test 3
	// - Test 4
	// Test 5
	//
	} elseif ($sibling_above['id'] == $node['parent']) {
		// higher the sorting of the current tree and all trees below
		// adjust sorting of the node above
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` + 1
			WHERE
				`sorting` >= :sorting
			AND
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// take current node out of the tree and turn it into a root node
		$sqlData = array(
			'root_node' => $node['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => 2,
			'level' => 1,
			'sorting' => $node['sorting']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// now we have to care about possible childs of the current node. let's start
		// with the parent adjustment.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling_above['id'],
			'old_parent' => (int)$node['id'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// now we have to adjust the lfts, rgts and the level
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1,
				`level` = `level` - 1
			WHERE
				`lft` > :lft
			AND
				`rgt` < :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array(
			'lft' => (int)$node['lft'],
			'rgt' => (int)$node['rgt'],
			'root_node' => (int)$node['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		
		
		// and at last task, we have to adjust the lfts and the rgts in the rest of the tree.
		// let's start with the lfts.
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 2
			WHERE
				`lft` > :lft
			AND
				`rgt` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array(
			'lft' => (int)$node['rgt'],
			'rgt' => (int)$node['rgt'],
			'root_node' => (int)$node['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// and now the rgts
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` - 2
			WHERE
				`rgt` > :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array(
			'rgt' => (int)$node['rgt'],
			'root_node' => (int)$node['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);			
	} else {
		throw new Utility_NestedSetException("Move not implemented");
	}
	
	return true;
}

/**
 * Moves node up in tree. Takes the navigation id as first
 * argument, the id of the node to move as second argument.
 * Returns boolean true.
 *
 * Attention: Cannot move nodes with lft < 2. 
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return bool
 */
public function moveUpInTree ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that we don't try to move a root node or a sub root node
	if ((int)$node['lft'] === 1 || (int)$node['lft'] === 2) {
		return $this->moveUpAcrossTrees($navigation, $node_id);
	}
	
	// get sibling above
	$sibling = $this->selectSiblingAbove($navigation, $node_id);
	
	// if there's no sibling, we cannot move above.
	if (empty($sibling)) {
		return true;
	}
	
	// if the sibling is the parent (no, no oedipus here) of the current node,
	// "jump" over it.
	if ($node['parent'] == $sibling['id']) {
		// get the sibling of the sibling
		$sibling_2nd = $this->selectSiblingAbove($navigation, $sibling['id']);
		
		// close the gap left by the current node
		// update rgt
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` - 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` >= :lft
			AND
				`rgt` <= :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$sibling['lft'],
			'rgt' => (int)$sibling['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// update lft
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` > :lft
			AND
				`rgt` <= :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$sibling['lft'],
			'rgt' => (int)$sibling['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// make room for the node in the new tree
		// update lft
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` > :lft
			AND
				`rgt` < :rgt
		";
		
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$sibling_2nd['lft'],
			'rgt' => (int)$sibling['rgt']
		);
		
		// prepare bind params
		$this->base->db->execute($sql, $bind_params);
		
		// update rgt
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` > :lft
			AND
				`rgt` < :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$sibling_2nd['lft'],
			'rgt' => (int)$sibling['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust lft etc. of moved node
		$sqlData = array(
			'parent' => (is_numeric($sibling['parent']) ? (int)$sibling['parent'] : null),
			'lft' => (int)$sibling['lft'],
			'rgt' => (int)$sibling['lft'] + 1,
			'level' => (int)$sibling['level']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// update lft and rgt of possible childs
		$sql = "
			UPDATE
				content_nodes
			SET
				lft = lft + 1,
				rgt = rgt + 1,
				level = level - 1
			WHERE
				root_node = :root_node
			AND
				lft > :lft
			AND
				rgt < :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$node['lft'],
			'rgt' => (int)$node['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// update the parent of possible childs
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
		";
		
		// prepare bind params
		$bind_params = array(
			'new_parent' => $sibling['id'],
			'old_parent' => $node['id']
		);
		
		// execute update
		$this->base->db->execute($sql, $bind_params);
		
	// if both are real siblings, turn the current node into
	// a child of the sibling
	} elseif (((int)$node['lft'] - (int)$sibling['rgt']) === 1) {
		// make room for the current node in the new sub tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`root_node` = :root_node
			AND
				`rgt` > :sibling_rgt
			AND
				`rgt` < :node_lft
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'sibling_rgt' => (int)$sibling['rgt'],
			'node_lft' => (int)$node['lft']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust the parent information of existing childs of the current
		// node
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`root_node` = :root_node
			AND
				`parent` = :old_parent
		";
		
		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling['id'],
			'root_node' => (int)$node['root_node'],
			'old_parent' => (int)$node['id'],
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// update parent, lft etc. of current node
		$sqlData = array(
			'root_node' => (int)$sibling['root_node'],
			'parent' => (is_numeric($sibling['id']) ? $sibling['id'] : null),
			'lft' => (int)$sibling['lft'] + 1,
			'rgt' => (int)$sibling['lft'] + 2,
			'level' => (int)$sibling['level'] + 1,
			'sorting' => (int)$sibling['sorting']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// adjust rgt of sibling
		$sqlData = array(
			'rgt' => (int)$node['rgt']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling['id']
		);
		
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	} else {
		// if the next sibling is more than one level deeper, we have so search for
		// the closest sibling that's only one level deeper 
		if (((int)$sibling['level'] - (int)$node['level']) > 1) {
			
			// get the node where to append the current node to
			$real_sibling = $this->selectSiblingAboveOneLevelDeeper($navigation, $node['id']);
			
			// adjust rgts in new node 
			$sql = "
				UPDATE
					`".WCOM_DB_CONTENT_NODES."`
				SET
					`rgt` = `rgt` + 2
				WHERE
					`root_node` = :root_node
				AND
					`rgt` > :real_sibling_rgt
				AND
					`rgt` < :node_lft
			";
			
			// prepare bind params
			$bind_params = array(
				'root_node' => $real_sibling['root_node'],
				'real_sibling_rgt' => $real_sibling['rgt'],
				'node_lft' => $node['lft']
			);
			
			// execute query
			$this->base->db->execute($sql, $bind_params);
			var_dump($real_sibling);
			// update node
			$sqlData = array(
				'parent' => $real_sibling['parent'],
				'lft' => $real_sibling['rgt'] + 1,
				'rgt' => $real_sibling['rgt'] + 2,
				'level' => $real_sibling['level']
			);
			
			// prepare where clause
			$where = " WHERE `id` = :id ";

			// prepare bind params
			$bind_params = array(
				'id' => (int)$node['id']
			);

			// execute update
			$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
			
			// take care of possible childs
			// first: update parent
			$sql = "
				UPDATE
					`".WCOM_DB_CONTENT_NODES."`
				SET
					`parent` = :new_parent
				WHERE
					`root_node` = :root_node
				AND
					`parent` = :old_parent
			";
			
			// prepare bind params
			$bind_params = array(
				'new_parent' => $node['parent'],
				'root_node' => $node['root_node'],
				'old_parent' => $node['id'],
			);
			
			// execute query
			$this->base->db->execute($sql, $bind_params);
			
			// update levels, rgts and lft
			$sql = "
				UPDATE
					`".WCOM_DB_CONTENT_NODES."`
				SET
					`level` = `level` - 1,
					`lft` = `lft` + 1,
					`rgt` = `rgt` + 1
				WHERE
					`root_node` = :root_node
				AND
					`lft` > :lft
				AND
					`rgt` < :rgt
			";
			
			// prepare bind params
			$bind_params = array(
				'root_node' => $node['root_node'],
				'lft' => $node['lft'],
				'rgt' => $node['rgt']
			);
			
			// execute query
			$this->base->db->execute($sql, $bind_params);
			
		} else {
			// update rgt of new parent
			$sql = "
				UPDATE
					`".WCOM_DB_CONTENT_NODES."`
				SET
					`rgt` = `rgt` + :diff
				WHERE
					`id` = :id
			";
			
			// prepare bind params
			$bind_params = array(
				'diff' => (int)$node['rgt'] - (int)$node['lft'] + 1,
				'id' => (int)$sibling['parent']
			);
			
			// execute update
			$this->base->db->execute($sql, $bind_params);
			
			// prepare sql data
			$sqlData = array(
				'parent' => (int)$sibling['parent'],
				'lft' => (int)$sibling['rgt'] + 1,
				'rgt' => (int)$sibling['rgt'] + 2,
				'level' => (int)$sibling['level'],
				'sorting' => (int)$sibling['sorting']
			);
			
			// prepare where clause
			$where = " WHERE `id` = :id ";

			// prepare bind params
			$bind_params = array(
				'id' => (int)$node['id']
			);

			// execute update
			$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
			
			// take care of possible childs
			$sql = "
				UPDATE
					`".WCOM_DB_CONTENT_NODES."`
				SET
					`parent` = :new_parent
				WHERE
					`root_node` = :root_node
				AND
					`parent` = :old_parent
			";
			
			// prepare bind params
			$bind_params = array(
				'new_parent' => $sibling['parent'],
				'root_node' => $node['root_node'],
				'old_parent' => $node['id'],
			);
			
			// execute query
			$this->base->db->execute($sql, $bind_params);
		}
	}
	
	return true;
}

/**
 * Moves node down across a tree. Takes the navigation id as first
 * argument, the id of the node to move as second argument. Returns
 * boolean true.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return bool
 */
public function moveDownAcrossTrees ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// get sibling above
	$sibling_above = $this->selectSiblingAbove($navigation, $node_id);
	
	// get sibling below
	$sibling_below = $this->selectSiblingBelow($navigation, $node_id);
	
	// make sure that we don't try to move within a tree
	if (is_array($sibling_below) &&  $sibling_below['root_node'] == $node['root_node'] && $node['lft'] > 1) {
		return $this->moveDownInTree($navigation, $node_id);
	}
	
	// if it's a root node and that one with the highest sorting,
	// let's move it at the beginning of the whole navigation structure
	if ((int)$node['lft'] === 1 && !is_array($sibling_below) && $this->selectMaxSorting($navigation) == $node['sorting']) {
		// get minimum sorting in the navigation structure
		$min_sorting = $this->selectMinSorting($navigation);
		
		// adjust sorting in the rest of the navigation structure
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` + 1
			WHERE
				`navigation` = :navigation
		";
		
		// prepare bind params
		$bind_params = array (
			'navigation' => $navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust sorting of the current node
		$sqlData = array(
			'sorting' => (int)$min_sorting
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
	// take current node out of its tree and create a new tree
	// the the current node as its root node
	} elseif ($node['lft'] > 1 && ($sibling_below['lft'] == 1 || !is_array($sibling_below))) {
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` - 2
			WHERE
				`root_node` = :root_node
			AND
				`rgt` > :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'rgt' => (int)$node['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// adjust sorting of all the trees
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` + 1
			WHERE
				`navigation` = :navigation
			AND
				`sorting` > :sorting
		";
		
		// prepare bind params
		$bind_params = array(
			'navigation' => (int)$navigation,
			'sorting' => (int)$node['sorting']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// turn current node into new root node
		$sqlData = array(
			'root_node' => (int)$node['id'],
			'parent' => null,
			'lft' => 1,
			'rgt' => 2,
			'level' => 1,
			'sorting' => (int)$node['sorting'] + 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	// node is a root node and the sibling below is a root node too.
	// so we have to turn the root node into a child of the sibling
	// below.
	} else {
		// decrease sorting in the whole navigation structure
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`navigation` = :navigation
			AND
				`sorting` > :sorting
		";
		
		// prepare bind params
		$bind_params = array(
			'navigation' => (int)$navigation,
			'sorting' => $node['sorting']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// make room in the new tree, adjust lfts
		$sql = "
			UPDATE 
				 `".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` > :lft
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$sibling_below['root_node'],
			'lft' => (int)$sibling_below['lft']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// make room in the new tree, adjust rgts
		$sql = "
			UPDATE 
				 `".WCOM_DB_CONTENT_NODES."`
			SET
				`rgt` = `rgt` + 2
			WHERE
				`root_node` = :root_node
			AND
				`lft` >= :lft
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$sibling_below['root_node'],
			'lft' => (int)$sibling_below['lft']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// update current node
		$sqlData = array(
			'root_node' => (int)$sibling_below['root_node'],
			'parent' => (int)$sibling_below['id'],
			'lft' => (int)$sibling_below['lft'] + 1,
			'rgt' => (int)$sibling_below['lft'] + 2,
			'level' => (int)$sibling_below['level'] + 1,
			'sorting' => (int)$sibling_below['sorting'] - 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	}
	
	return true;
}

/**
 * Moves node down in tree. Takes the navigation id as first argument,
 * the id of the node to move as second argument. Returns boolean true.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return bool
 */
public function moveDownInTree ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// get sibling above
	$sibling_above = $this->selectSiblingAbove($navigation, $node_id);
	
	// get sibling below
	$sibling_below = $this->selectSiblingBelow($navigation, $node_id);
	
	// make sure that we don't try to move root nodes across multiple trees
	if ((int)$node['lft'] === 1 && (int)$sibling_below['root_node'] !== (int)$node['root_node']) {
		return $this->moveDownAcrossTrees($navigation, $node_id);
	}
	// make sure that we don't try to move across multiple trees
	if ((int)$node['level'] === 2 && (!is_array($sibling_below) || (int)$sibling_below['root_node'] !== (int)$node['root_node'])) {
		return $this->moveDownAcrossTrees($navigation, $node_id);
	}
	
	// Handles move if the current node is at the end of a subtree
	// or at the end of the whole tree and the level difference
	// between the current node and the sibling below is bigger
	// than one.
	// 
	// Test 1
	// - Test 2
	// - - Test 3 <---
	// Test 4
	// 
	// Moving test 3 should result in:
	//
	// Test 1
	// - Test 2
	// - Test 3 <---
	// Test 4
	//
	if ((int)$sibling_below['level'] <= ($node['level'] - 1)) {
		// get sibling one level higher
		$sibling_above = $this->selectSiblingAboveOneLevelHigher($navigation, $node_id);
		
		// update level, lfts and rgts of subnode
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`level` = `level` - 1,
				`lft` = `lft` + 1,
				`rgt` = `rgt` + 1
			WHERE
				`root_node` = :root_node
			AND
				`lft` >= :lft
			AND
				`rgt` <= :rgt
		";
		
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['root_node'],
			'lft' => (int)$node['lft'],
			'rgt' => (int)$node['rgt']
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// take sibling out of tree
		$sqlData = array(
			'rgt' => (int)$sibling_above['rgt'] - 2
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_above['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

		// turn current node into "root" of sub tree
		$sqlData = array(
			'rgt' => (int)$sibling_above['rgt'],
			'parent' => (int)$sibling_above['parent']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
	// nodes with a lft of 1 are root nodes, we have to place them somewhere
	// in the tree and have to create a new root node. so let's change its
	// position with that one of the sibling below.
	//
	// Test 1 <---
	// - Test 2 
	// - - Test 3
	// 
	// moving 'Test 1' should result in:
	//
	// Test 2
	// - Test 1 <---
	// - - Test 3
	//
	} elseif ((int)$node['lft'] === 1 && $sibling_below['lft'] > 1) {
		// decrease level of all subnodes of the sibling below
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 1,
				`rgt` = `rgt` + 1,
				`level` = `level` - 1
			WHERE
				`root_node` = :root_node
			AND
				`rgt` <= :rgt
		";

		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$sibling_below['root_node'],
			'rgt' => (int)$sibling_below['rgt']
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);

		// turn sibling below into root node
		$sqlData = array(
			'parent' => (int)$sibling_below['id'],
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['lft'] + 1,
			'level' => (int)$sibling_below['level']
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

		// turn root node into replacement of sibling below
		$sqlData = array(
			'parent' => null,
			'lft' => 1,
			'rgt' => (int)$node['rgt']
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

		// update root node in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
		";

		// prepare bind params
		$bind_params = array(
			'new_root_node' => (int)$sibling_below['id'],
			'old_root_node' => (int)$node['id']
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// update parent in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`root_node` = :root_node
			AND
				`navigation` = :navigation
		";

		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling_below['id'],
			'old_parent' => (int)$node['id'],
			'root_node' => (int)$sibling_below['id'],
			'navigation' => (int)$navigation
		);

		// execute query
		$this->base->db->execute($sql, $bind_params);		

	// if the current node and the following node are on the same level,
	// turn the current node into a child of the following node.
	//
	// Test 1
	// - Test 2 <---
	// - Test 3
	//
	// moving 'Test 3' should result in:
	//
	// Test 1
	// - Test 3
	// - - Test 2 <---	
	//
	} elseif ((int)$node['parent'] === (int)$sibling_below['parent']) {
		// turn the current node into a child of the following node
		$sqlData = array(
			'parent' => (int)$sibling_below['id'],
			'lft' => (int)$sibling_below['lft'] - 1,
			'rgt' => (int)$sibling_below['lft'],
			'level' => (int)$sibling_below['level'] + 1
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
		// update the lft of the new parent of the current node
		$sqlData = array(
			'lft' => $sibling_below['lft'] - 2
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);

	// that handles moves if the sibling below is a child of the current node and
	// the sibling below the sibling below is a child of the sibling below -- everything's
	// clear? ;)
	//
	// Test 1
	// - Test 2 <---
	// - - Test 3
	// - - - Test 4
	// - - Test 5
	//
	// moving 'Test 2' should result in:
	//
	// Test 1
	// - Test 3 
	// - - Test 2 <---
	// - - Test 4
	// - - Test 5
	//
	} elseif ($node['id'] == $sibling_below['parent'] && $node['lft'] > 1) {

		// turn childs of the current node into siblings. first step is to
		// update the parent:
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
			AND
				`root_node` = :root_node
			AND
				`navigation`= :navigation
		";

		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling_below['id'],
			'old_parent' => (int)$node['id'],
			'root_node' => (int)$node['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);
		
		// the next step is to higher the lfts and rgts and
		// to lower the level
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` + 1,
				`rgt` = `rgt` + 1,
				`level` = `level` - 1
			WHERE
				`lft` > :lft
			AND
				`rgt` < :rgt
			AND
				`root_node` = :root_node
			AND
				`navigation`= :navigation
		";

		// prepare bind params
		$bind_params = array(
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt'],
			'root_node' => (int)$node['root_node'],
			'navigation' => (int)$navigation
		);
		
		// execute query
		$this->base->db->execute($sql, $bind_params);		 
		
		// swap the current node with the sibling below
		$sqlData = array(
			'parent' => (int)$sibling_below['id'],
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['lft'] + 1,
			'level' => (int)$sibling_below['level']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);		
		
		// swap the sibling below with the current node
		$sqlData = array(
			'parent' => (int)$node['parent'],
			'lft' => (int)$node['lft'],
			'rgt' => $node['rgt'],
			'level' => (int)$node['level']
		);
		
		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);

		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
		
	} else {
		throw new Utility_NestedSetException("Move not implemented");
	}
	
	return true;
}

/**
 * Takes root node out of a navigation and puts it at the end of
 * another navigation. Takes the id of the "old" navigation as
 * first argument, the id of the root node to move as second
 * argument and the id of the new navigation as third and last
 * argument.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @param int New navigation id
 * @return bool
 */
public function changeNavigationOfRootNode ($navigation, $node_id, $new_navigation)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	// input check
	if (empty($new_navigation) || !is_numeric($new_navigation)) {
		throw new Utility_NestedSetException("Input for parameter new_navigation is expected to be numeric");
	}
	
	// if new and old navigation are the same, skip the whole procedure
	if ($new_navigation == $navigation) {
		return true;
	}
		
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that this one is a root node
	if ((int)$node['lft'] !== 1) {
		return $this->changeNavigationOfNodeInTree($navigation, $node_id);
	}
	
	// and we need the sibling below too
	$sibling_below = $this->selectSiblingBelow($navigation, $node['id']);
	
	// handles move if the sibling below is not a root node
	// 
	// Test 1 <---
	// - Test 2
	//	
	if ($sibling_below['lft'] > 1) {
		// now we need to fix the old tree. turn the first child of the current node
		// into the new root node. lot of work :(
	
		// update level
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`level` = `level` - 1
			WHERE
				`root_node` = :root_node
			AND
				`lft` >= :lft
			AND
				`rgt` <= :rgt
		";
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id'],
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update rgt and lft in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1
			WHERE
				`root_node` = :root_node
		";
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update lfts and rgts in the rest of the tree that's not
		// within the subtree of the new root node... um, what!?
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1
			WHERE
				`root_node` = :root_node
			AND
				`rgt` >= :rgt
		"; 
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id'],
			'rgt' => (int)$sibling_below['rgt']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update root node in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
		";
	
		// prepare bind params
		$bind_params = array(
			'new_root_node' => (int)$sibling_below['id'],
			'old_root_node' => (int)$node['id']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update parents in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
		";
	
		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling_below['id'],
			'old_parent' => (int)$node['id']
		);
	
		$this->base->db->execute($sql, $bind_params);
		
		// update new root node
		$sqlData = array(
			'parent' => null,
			'rgt' => (int)$node['rgt'] - 2
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);
	
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
	// handles move of root nodes if the sibling below is 
	// a root node too.
	//
	// Test 1 <--
	// Test 2
	// 
	} elseif ($sibling_below['lft'] == 1 || empty($sibling_below)) {
		// update sorting
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`sorting` > :sorting
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);
	
		$this->base->db->execute($sql, $bind_params);		
	} else {
		throw new Utility_NestedSetException("Move not implemented");
	}
	
	// put current node at the end of the new navigation
	$sqlData = array(
		'navigation' => $new_navigation,
		'root_node' => $node['id'],
		'parent' => null,
		'lft' => 1,
		'rgt' => 2,
		'level' => 1,
		'sorting' => $this->selectMaxSorting($new_navigation) + 1
	); 
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$node_id
	);
	
	// execute update
	$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
	return true;
}

/**
 * Takes node out of a navigation and puts it at the end of
 * another navigation. Takes the id of the "old" navigation as
 * first argument, the id of the root node to move as second
 * argument and the id of the new navigation as third and last
 * argument.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @param int New navigation id
 * @return bool
 */
public function changeNavigationOfNodeInTree ($navigation, $node_id, $new_navigation)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node is expected to be numeric");
	}
	if (empty($new_navigation) || !is_numeric($new_navigation)) {
		throw new Utility_NestedSetException("Input for parameter new_navigation is expected to be numeric");
	}
		
	// if new and old navigation are the same, skip the whole procedure
	if ($new_navigation == $navigation) {
		return true;
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that this one is not a root node
	if ((int)$node['lft'] === 1) {
		return $this->changeNavigationOfRootNode($navigation, $node_id, $new_navigation);
	}
	
	// update lfts and rgts within the subtree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` - 1,
			`rgt` = `rgt` - 1,
			`level` = `level` - 1
		WHERE
			`root_node` = :root_node
		AND
			`lft` > :lft
		AND
			`rgt` < :rgt
	";
	
	// prepare bind aprams
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'lft' => (int)$node['lft'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// update lfts in the rest of the tree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` - 2
		WHERE
			`root_node` = :root_node
		AND
			`lft` > :rgt
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);	

	// update rgts in the rest of the tree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`rgt` = `rgt` - 2
		WHERE
			`root_node` = :root_node
		AND
			`rgt` > :rgt
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);	
	
	// put current node at the end of the new navigation
	$sqlData = array(
		'navigation' => $new_navigation,
		'root_node' => $node['id'],
		'parent' => null,
		'lft' => 1,
		'rgt' => 2,
		'level' => 1,
		'sorting' => $this->selectMaxSorting($new_navigation) + 1
	); 
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$node_id
	);
	
	// execute update
	$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
	return true;
}

/**
 * Removes root node. Takes the navigation id as first argument,
 * the id of the root node to delete as second argument. Returns
 * amount of affected rows.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return int Amount of affected rows
 */
public function deleteRootNode ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that this one is a root node
	if ((int)$node['lft'] !== 1) {
		return $this->deleteNode($navigation, $node_id);
	}
	
	// and we need the sibling below too
	$sibling_below = $this->selectSiblingBelow($navigation, $node['id']);
	
	// handles deletion if the sibling below is not a root node
	// 
	// Test 1 <---
	// - Test 2
	//	
	if ($sibling_below['lft'] > 1) {
		// now we need to fix the old tree. turn the first child of the current node
		// into the new root node. lot of work :(
	
		// update level
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`level` = `level` - 1
			WHERE
				`root_node` = :root_node
			AND
				`lft` >= :lft
			AND
				`rgt` <= :rgt
		";
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id'],
			'lft' => (int)$sibling_below['lft'],
			'rgt' => (int)$sibling_below['rgt']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update rgt and lft in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1
			WHERE
				`root_node` = :root_node
		";
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update lfts and rgts in the rest of the tree that's not
		// within the subtree of the new root node... um, what!?
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`lft` = `lft` - 1,
				`rgt` = `rgt` - 1
			WHERE
				`root_node` = :root_node
			AND
				`rgt` >= :rgt
		"; 
	
		// prepare bind params
		$bind_params = array(
			'root_node' => (int)$node['id'],
			'rgt' => (int)$sibling_below['rgt']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update root node in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`root_node` = :new_root_node
			WHERE
				`root_node` = :old_root_node
		";
	
		// prepare bind params
		$bind_params = array(
			'new_root_node' => (int)$sibling_below['id'],
			'old_root_node' => (int)$node['id']
		);
	
		// execute query
		$this->base->db->execute($sql, $bind_params);
	
		// update parents in the whole tree
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`parent` = :new_parent
			WHERE
				`parent` = :old_parent
		";
	
		// prepare bind params
		$bind_params = array(
			'new_parent' => (int)$sibling_below['id'],
			'old_parent' => (int)$node['id']
		);
	
		$this->base->db->execute($sql, $bind_params);
		
		// update new root node
		$sqlData = array(
			'parent' => null,
			'rgt' => (int)$node['rgt'] - 2
		);

		// prepare where clause
		$where = " WHERE `id` = :id ";

		// prepare bind params
		$bind_params = array(
			'id' => (int)$sibling_below['id']
		);
	
		// execute update
		$this->base->db->update(WCOM_DB_CONTENT_NODES, $sqlData, $where, $bind_params);
	
		// remove the current node
		// prepare where clause
		$where = " WHERE `id` = :id ";
	
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
	
		// execute update
		return $this->base->db->delete(WCOM_DB_CONTENT_NODES, $where, $bind_params);
	
	// handles deletion of root nodes if the sibling below is 
	// a root node too.
	//
	// Test 1 <--
	// Test 2
	// 
	} elseif ($sibling_below['lft'] == 1 || empty($sibling_below)) {
		// update sorting
		$sql = "
			UPDATE
				`".WCOM_DB_CONTENT_NODES."`
			SET
				`sorting` = `sorting` - 1
			WHERE
				`sorting` > :sorting
			AND
				`navigation` = :navigation
		";
	
		// prepare bind params
		$bind_params = array(
			'sorting' => (int)$node['sorting'],
			'navigation' => (int)$navigation
		);
	
		$this->base->db->execute($sql, $bind_params);		
		
		// remove the current node
		// prepare where clause
		$where = " WHERE `id` = :id ";
		
		// prepare bind params
		$bind_params = array(
			'id' => (int)$node['id']
		);
	
		// execute update
		return $this->base->db->delete(WCOM_DB_CONTENT_NODES, $where, $bind_params);
				
	} else {
		throw new Utility_NestedSetException("Deletion not implemented");
	}
}

/**
 * Removes node. Takes the navigation id as first argument,
 * the id of the node to delete as second argument. Returns amount
 * of affected rows.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Node id
 * @return int Amount of affected rows
 */
public function deleteNodeInTree ($navigation, $node_id)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (empty($node_id) || !is_numeric($node_id)) {
		throw new Utility_NestedSetException("Input for parameter node_id is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($node_id, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$node = $this->selectNode($node_id);
	
	// make sure that this one is a root node
	if ((int)$node['lft'] === 1) {
		return $this->deleteRootNode($navigation, $node_id);
	}
	
	// update lfts and rgts within the subtree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` - 1,
			`rgt` = `rgt` - 1,
			`level` = `level` - 1
		WHERE
			`root_node` = :root_node
		AND
			`lft` > :lft
		AND
			`rgt` < :rgt
	";
	
	// prepare bind aprams
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'lft' => (int)$node['lft'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// update lfts in the rest of the tree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`lft` = `lft` - 2
		WHERE
			`root_node` = :root_node
		AND
			`lft` > :rgt
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// update direct childs of the node to be deleted
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`parent` = :new_parent
		WHERE
			`root_node` = :root_node
		AND
			`parent` = :old_parent
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'old_parent' => (int)$node['id'],
		'new_parent' => (int)$node['parent'] 
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// update rgts in the rest of the tree
	$sql = "
		UPDATE
			`".WCOM_DB_CONTENT_NODES."`
		SET
			`rgt` = `rgt` - 2
		WHERE
			`root_node` = :root_node
		AND
			`rgt` > :rgt
	";
	
	// prepare bind params
	$bind_params = array(
		'root_node' => (int)$node['root_node'],
		'rgt' => (int)$node['rgt']
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);	
	
	// remove the current node
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$node['id']
	);
	
	// execute update
	return $this->base->db->delete(WCOM_DB_CONTENT_NODES, $where, $bind_params);	
}


/**
 * Selects node. Takes the node id as first argument. Returns
 * array.
 *
 * @throws Utility_NestedSetException
 * @param int Node id
 * @return array
 */
public function selectNode ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Utility_NestedSetException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`id` = :id
		LIMIT 
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more nodes. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>navigation, int, optional: Navigation id</li>
 * <li>root_node, int, optional: Root node id</li>
 * <li>parent, int, optional: Parent node id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Utility_NestedSetException
 * @param array Select params
 * @return array
 */
public function selectNodes ($params = array())
{
	// define some vars
	$navigation = null;
	$root_node = null;
	$parent = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Utility_NestedSetException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'navigation':
			case 'root_node':
			case 'parent':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Utility_NestedSetException("Unknown parameter $_key");
		}
	}
	
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			1
	";

	// prepare where clauses
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `navigation` = :navigation ";
		$bind_params['navigation'] = (int)$navigation;
	}
	if (!empty($root_node) && is_numeric($root_node)) {
		$sql .= " AND `root_node` = :root_node ";
		$bind_params['root_node'] = (int)$root_node;
	}
	if (!empty($parent) && is_numeric($parent)) {
		$sql .= " AND `parent` = :parent ";
		$bind_params['parent'] = (int)$parent;
	}
	
	// add sorting
	$sql .= " ORDER BY `sorting`, `lft` ";
	
	// add limits
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}

	return $this->base->db->select($sql, 'multi', $bind_params);	
}

/**
 * Method to count the nodes. Takes key=>value array
 * with count params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>navigation, int, optional: Navigation id</li>
 * <li>root_node, int, optional: Root node id</li>
 * <li>parent, int, optional: Parent node id</li>
 * </ul>
 * 
 * @throws Utility_NestedSetException
 * @param array Count params
 * @return int
 */
public function countNodes ($params = array())
{
	// define some vars
	$navigation = null;
	$root_node = null;
	$parent = null;
	$lft_bigger_than = null;
	$rgt_smaller_than = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Utility_NestedSetException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'navigation':
			case 'root_node':
			case 'parent':
			case 'lft_bigger_than':
			case 'rgt_smaller_than':
					$$_key = (int)$_value;
				break;
			default:
				throw new Utility_NestedSetException("Unknown parameter $_key");
		}
	}
	
	$sql = "
		SELECT
			COUNT(*) AS `total` 
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			1
	";

	// prepare where clauses
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `navigation` = :navigation ";
		$bind_params['navigation'] = (int)$navigation;
	}
	if (!empty($root_node) && is_numeric($root_node)) {
		$sql .= " AND `root_node` = :root_node ";
		$bind_params['root_node'] = (int)$root_node;
	}
	if (!empty($parent) && is_numeric($parent)) {
		$sql .= " AND `parent` = :parent ";
		$bind_params['parent'] = (int)$parent;
	}
	if (!empty($lft_bigger_than) && is_numeric($lft_bigger_than)) {
		$sql .= " AND `lft` > :lft_bigger_than ";
		$bind_params['lft_bigger_than'] = (int)$lft_bigger_than;
	}
	if (!empty($rgt_smaller_than) && is_numeric($rgt_smaller_than)) {
		$sql .= " AND `rgt` < :rgt_smaller_than ";
		$bind_params['rgt_smaller_than'] = (int)$rgt_smaller_than;
	}

	return $this->base->db->select($sql, 'field', $bind_params);	
}

/**
 * Selects node above reference node. Takes the navigation id as
 * first argument, the id of the reference node as second argument.
 * Returns array with complete information about sibling.
 * 
 * @throws Utility_NestedSetExceptio
 * @param int Navigation id
 * @param int Reference node id
 * @return array
 */
protected function selectSiblingAbove ($navigation, $reference)
{
	// input check
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for paramter reference is expected to be numeric");
	}
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for paramter navigation is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get current node
	$node = $this->selectNode($reference);
	
	// prepare query to get sibling above current node
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`navigation` = :navigation
		AND
			(
				`root_node` = :root_node
			  AND
				`lft` < :lft	
			)
		  OR
			(
				`sorting` < :sorting
			)
		ORDER BY
			`sorting` DESC,
			`lft` DESC
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation,
		'root_node' => (int)$node['root_node'],
		'sorting' => (int)$node['sorting'],
		'lft' => (int)$node['lft'] 
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects sibling above the reference node that's one level
 * deeper than the current node. Takes the navigation id as first
 * argument, the reference node id as second argument. Returns
 * array with full node information.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @return array
 */
protected function selectSiblingAboveOneLevelDeeper ($navigation, $reference)
{
	// input check
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for paramter reference is expected to be numeric");
	}
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for paramter navigation is expected to be numeric");
	}
		
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get current node
	$node = $this->selectNode($reference);
	
	// prepare query
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`navigation` = :navigation
		AND
			`level` = :level
		AND
			(
				`lft` < :lft
			  AND
				`root_node` = :root_node
			OR
				`sorting` < :sorting
			)
		ORDER BY
			`sorting` DESC,
			`lft` DESC
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation,
		'level' => (int)$node['level'] + 1,
		'lft' => (int)$node['lft'],
		'root_node' => (int)$node['root_node'],
		'sorting' => (int)$node['sorting']
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects sibling above the reference node that's one level
 * higher than the current node. Takes the navigation id as 
 * first argument, the id of the reference node as second
 * argument. Returns array with full node information.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @return array
 */
protected function selectSiblingAboveOneLevelHigher ($navigation, $reference)
{
	// input check
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for paramter reference is expected to be numeric");
	}
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for paramter navigation is expected to be numeric");
	}
	
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get current node
	$node = $this->selectNode($reference);
	
	// prepare query
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			navigation = :navigation
		AND
			`level` = :level
		AND
			(
				(
					`lft` < :lft
				AND
					`root_node` = :root_node
				)
			OR
				`sorting` < :sorting
			)
		ORDER BY
			`sorting` DESC,
			`lft` DESC
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation,
		'level' => (int)$node['level'] - 1,
		'lft' => (int)$node['lft'],
		'root_node' => (int)$node['root_node'],
		'sorting' => (int)$node['sorting']
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Select sibling below reference node. Takes the navigation id
 * as first argument and the id of the reference node as second
 * argument. Returns array with full node information.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @param int Reference node id
 * @return array
 */
protected function selectSiblingBelow ($navigation, $reference)
{
	// input check
	if (empty($reference) || !is_numeric($reference)) {
		throw new Utility_NestedSetException("Input for paramter reference is expected to be numeric");
	}
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for paramter navigation is expected to be numeric");
	}
		
	// make sure that the node exists, otherwise we're lost
	if (!$this->node_exists($reference, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get current node
	$node = $this->selectNode($reference);
	
	// prepare query to get sibling below current node (not for root nodes)
	$sql = "
		SELECT
			`id`,
			`navigation`,
			`root_node`,
			`parent`,
			`lft`,
			`rgt`,
			`level`,
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`navigation` = :navigation
		  AND
			(
				`root_node` = :root_node
		 	  AND
				`lft` > :lft
			)
		  OR
			(
				`sorting` > :sorting
			)
	ORDER BY
		`sorting`, `lft`
	LIMIT
		1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation,
		'root_node' => (int)$node['root_node'],
		'lft' => (int)$node['lft'],
		'sorting' => (int)$node['sorting']
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Returns highest value in the sorting column for the
 * given navigation. Takes the navigation id as first
 * argument. Returns int.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @return int Sorting value
 */
protected function selectMaxSorting ($navigation)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`navigation` = :navigation
		ORDER BY
			`sorting` DESC
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Returns smallest value in the sorting column for the
 * given navigation. Takes the navigation id as first
 * argument. Returns int.
 * 
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @return int Sorting value
 */
protected function selectMinSorting ($navigation)
{
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`sorting`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			`navigation` = :navigation
		ORDER BY
			`sorting`
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Checks whether a node exists or not. Takes the node id as
 * first argument and the navigation id as optional second argument.
 * Returns bool.
 * 
 * @throws Utility_NestedSetException
 * @param int Node id
 * @param int Navigation id
 * @return bool
 */
public function node_exists ($node, $navigation = null)
{
	// input check
	if (empty($node) && !is_numeric($node)) {
		throw new Utility_NestedSetException("Input for parameter node is expected to be numeric");
	}
	if (!empty($navigation) && !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			`".WCOM_DB_CONTENT_NODES."`
		WHERE
			1
	";
	
	$bind_params = array();
	
	// add where clauses
	if (!empty($node) && is_numeric($node)) {
		$sql .= " AND `id` = :id ";
		$bind_params['id'] = $node;
	}
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `navigation` = :navigation ";
		$bind_params['navigation'] = $navigation;
	}
	
	// evaluate query
	if ((int)$this->base->db->select($sql, 'field', $bind_params) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Tests whether a node is a root node or not. Takes the node id as
 * first argument and the navigation id as optional second argument.
 * Returns bool.
 * 
 * @throws Utility_NestedSetException
 * @param int Node id
 * @param int Navigation id
 * @return bool
 */
public function root_node ($node, $navigation = null)
{
	// input check
	if (empty($node) && !is_numeric($node)) {
		throw new Utility_NestedSetException("Input for parameter node is expected to be numeric");
	}
	if (!empty($node) && !is_numeric($node)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	if (!$this->node_exists($node, $navigation)) {
		throw new Utility_NestedSetException("Node does not exist");
	}
	
	// get node
	$result = $this->selectNode($node);
	
	// evaluate result
	if ($result['root_node'] == $result['id']) {
		return true;
	} else {
		return false;
	}
}

/**
 * Executes consistency check on a a navigationt tree. Takes the navigation
 * id as first argument. Returns bool.
 *
 * @throws Utility_NestedSetException
 * @param int Navigation id
 * @return bool
 */
public function testConsistency ($navigation)
{
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Utility_NestedSetException("Input for parameter navigation is expected to be numeric");
	}
	
	// get the whole tree
	$whole_tree = $this->selectNodes(array('navigation' => $navigation));
	
	// execute sanity test
	if (!$this->testSanity($whole_tree)) {
		throw new Utility_NestedSetException("Consistency test failed at sanity test");
	}
	
	// execute root node test
	if (!$this->testForRootNodes($whole_tree)) {
		throw new Utility_NestedSetException("Consistency test failed at root node test");
	}
	
	// execute nested set test
	if (!$this->testNestedSets($whole_tree)) {
		throw new Utility_NestedSetException("Consistency test failed at nested set test");
	}
	
	return true;
}

/**
 * Tests if all the numbers and identifiers in the navigation tree seem to make sense.
 * Takes an array with all nodes of a navigation (as returned by selectNodes()) as
 * first argument. Returns bool.
 *
 * @throws Utility_NestedSetException
 * @param array Navigation tree
 * @return bool
 */
protected function testSanity ($whole_tree)
{
	// input check
	if (!is_array($whole_tree)) {
		throw new Utility_NestedSetException("Input for parameter whole_tree is expected to be an array");
	}
	
	foreach ($whole_tree as $_node) {
		if (empty($_node['id']) || $_node['id'] < 1) {
			return false;
		}
		if (empty($_node['root_node']) || $_node['root_node'] < 1) {
			return false;
		}
		if (empty($_node['lft']) || $_node['lft'] < 1) {
			return false;
		}
		if (empty($_node['rgt']) || $_node['rgt'] < 2) {
			return false;
		}
		if ($_node['lft'] >= $_node['rgt']) {
			return false;
		}
		if ((!is_null($_node['parent']) && $_node['parent'] < 1) || $_node['parent'] == $_node['id']) {
			return false;
		}
		if (empty($_node['level']) || $_node['level'] < 1) {
			return false;
		}
		if ($_node['id'] == $_node['root_node'] && (!empty($_node['parent']) || $_node['lft'] != 1 || $_node['level'] != 1)) {
			return false;
		}
	}
	
	return true;
}

/**
 * Tests if there's at least one root node in the navigation tree. Takes an array
 * with all nodes of a navigation (as returned by selectNodes()) as first argument.
 * Returns bool.
 *
 * @throws Utility_NestedSetException
 * @param array Navigation tree
 * @return bool
 */
protected function testForRootNodes ($whole_tree)
{
	// input check
	if (!is_array($whole_tree)) {
		throw new Utility_NestedSetException("Input for parameter whole_tree is expected to be an array");
	}
	
	// if there's no node at all, we can skip this test
	if (count($whole_tree) < 1) {
		return true;
	}
	
	foreach ($whole_tree as $_node) {
		if (!empty($_node['id']) && $_node['id'] == $_node['root_node'] && $_node['lft'] == 1) {
			return true;
		}
	}
	
	return false;
}

/**
 * Tests if the nested set structure is valid. Takes an array with all nodes
 * of a navigation (as returned by selectNodes()) as first argument. Returns
 * bool.
 * 
 * @throws Utility_NestedSetException
 * @param array Navigation tree
 * @return bool
 */
protected function testNestedSets ($whole_tree)
{
	// input check
	if (!is_array($whole_tree)) {
		throw new Utility_NestedSetException("Input for parameter whole_tree is expected to be an array");
	}
	
	// if there's no node at all, we can skip this test
	if (count($whole_tree) < 1) {
		return true;
	}
	
	$last_node = array();
	foreach ($whole_tree as $_node) {
		
		// if there's no last node, the current node must be a root node
		if (empty($last_node)) {
			if ($_node['id'] != $_node['root_node'] || $_node['lft'] != 1 || !is_null($_node['parent'])) {
				Base_Error::setMessage("First node is not root node");
				return false;
			}

			$last_node = $_node;
			continue;
		}
		
		if ($_node['lft'] == $last_node['rgt']) {
			Base_Error::setMessage("Lft current node may not match rgt of last node");
			return false;
		}
				
		// if the root node of the last node is not the same as the root node
		// of the current node, the current node must be a root node.
		if ($last_node['root_node'] != $_node['root_node'] && $_node['id'] == $_node['root_node']) {
			
			// if the rgt minus the lft of the last node is bigger than one, it cannot be the last
			// node in its tree. therefore something must be wrong...
			if (($last_node['rgt'] - $last_node['lft']) > 1) {
				Base_Error::setMessage("Rgt of last node is not lft of current node minus 1");
				return false;
			}
			
			// if the sorting of the current node minus the sorting of the last node is not
			// one, something must be wrong.
			if (($_node['sorting'] - $last_node['sorting']) != 1) {
				Base_Error::setMessage("Sorting of last node is not sorting of current node plus 1");
				return false;
			}
			
			// now, go to the next node
			$last_node = $_node;
			continue;
		}
		
		// if the lft of the current node minus the lft of the last node equals 1, 
		// the last node must be the parent of the current node and they must share
		// the same root node and sorting. the level of the current node minus the
		// level of the last node has to be one too.
		if (($_node['lft'] - $last_node['lft']) == 1) {
			if ($last_node['id'] != $_node['parent'] || is_null($_node['parent'])) {
				Base_Error::setMessage("Parent does not match parent of last node ");
				return false;
			}
			if ($last_node['root_node'] != $_node['root_node']) {
				Base_Error::setMessage("Root node does not match root node of last node");
				return false;
			}
			if (($_node['level'] - $last_node['level']) != 1) {
				Base_Error::setMessage("Level of last node is not level of current node plus 1");
				return false;
			}
			if ($last_node['sorting'] != $_node['sorting']) {
				Base_Error::setMessage("Sorting does not match sorting of last node");
				return false;
			}
		}
		
		// if the lft of the current node minus the rgt of the last node 
		// equals 1, the last node must be a sibling of the current node.
		// therefore they must have the same level, the same root node, 
		// the same parent and the same sorting.
		if (($_node['lft'] - $last_node['rgt']) == 1) {
			if ($last_node['level'] != $_node['level']) {
				Base_Error::setMessage("Level does not match level of last node");
				return false;
			}
			if ($last_node['root_node'] != $_node['root_node']) {
				Base_Error::setMessage("Root node does not match root node of last node");
				return false;
			}
			if ($last_node['parent'] != $_node['parent'] || is_null($_node['parent'])) {
				Base_Error::setMessage("Parent does not match parent of last node");
				return false;
			}
			if ($last_node['sorting'] != $_node['sorting']) {
				Base_Error::setMessage("Sorting does not match sorting of last node");
				return false;
			}
		}
		
		// if the lft of the current node minus the lft of the last node is
		// bigger than one and the lft of the current node minus the rgt of
		// the last node, the level of the current node must be smaller than
		// the level of the last node.
		if (($_node['lft'] - $last_node['lft']) > 1 && ($_node['lft'] - $last_node['rgt']) > 1) {
			if ($_node['level'] >= $last_node['level']) {
				Base_Error::setMessage("Level is not smaller than the level of the last node");
				return false;
			}
			
			
			// look for the sibling above of the current node
			$sibling_found = false;
			foreach ($whole_tree as $_possible_sibling) {
				if ($_node['root_node'] != $_possible_sibling['root_node']) {
					continue;
				}
				
				if ($_node['lft'] == ($_possible_sibling['rgt'] + 1)) {
					// mark that we've found a sibling
					$sibling_found = true;
					
					// let's compare the current node with the possible sibling.
					if ($_node['parent'] != $_possible_sibling['parent']) {
						Base_Error::setMessage("Parent does not match parent of possible sibling");
						return false;
					}
					if ($_node['level'] != $_possible_sibling['level']) {
						Base_Error::setMessage("Level does not match level of possible sibling");
						return false;
					}
					if ($_node['sorting'] != $_possible_sibling['sorting']) {
						Base_Error::setMessage("Sorting does not match sorting of possible sibling");
						return false;
					}
					
					break 2;
				}
			}
			
			// if there was no sibling found, there's something wrong.
			if (!$sibling_found) {
				Base_Error::setMessage("Sibling was not found");
				return false;
			}
		} 
		
		$last_node = $_node;
	}
	
	return true;
}

// end of class
}

class Utility_NestedSetException extends Exception { }

?>