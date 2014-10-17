<?php

class DB {
	private $dbc;
	private $result;
	public $table = '';
	
	function __construct() {
		$this->dbc = new mysqli("HOSTNAME", "USERNAME", "PASSWORD", "DBNAME");
		
		if ($this->dbc->connect_errno) {
			die("Failed to connect to MySQL: (" . $this->dbc->connect_errno . ") " . $this->dbc->connect_error);
		}
	}
	
	// Runs a query that doesn't have a return value
	function runQuery($query) {
		$sql = $query;
		
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}
	}

	// Runs a query that does return the value
	function getQuery($query) {
		$sql = $query;
		
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}

		// Get results
		$rows = array();
		while ($row = $this->result->fetch_assoc()) {
			$rows[] = $row;
		}
		
		$this->result->close();
		return $rows;
	}

	// Gets a single record
	function getSingle($table, $column, $value) {

		// SQL query
		$sql = "SELECT * FROM " . $table . " WHERE " . $column . "='" . $value . "'";

		// Check for errors
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}

		// Get results
		$row = $this->result->fetch_assoc();
		
		$this->result->close();
		
		// Return results
		return $row;
	}

	// Get a whole table
	function getTable($table, $sort, $order) {
		if (!$sort == "") {	
			$sortby = 'ORDER BY ' . $sort . '' ; 
			if ($order <> "") {	$orderby = "DESC"; } else { $orderby = ''; }
		} else {
			$sortby = '';
			$orderby = '';
		}
		
		
		$sql = "SELECT * FROM " . $table . " " . $sortby . " " . $orderby;
		
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}
		
		$rows = array();
		while ($row = $this->result->fetch_assoc()) {
			$rows[] = $row;
		}
		
		$this->result->close();
		return $rows;
	}

	// Get selection
	function getWhere($table, $column, $value) {
		$sql = "SELECT * FROM " . $table . " WHERE " . $column . " = '" . $value . "'";
		
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}
		
		$rows = array();
		while ($row = $this->result->fetch_assoc()) {
			$rows[] = $row;
		}
		
		$this->result->close();
		return $rows;
	}

	// Updates the error log.
	function insertLog($user, $eventtype, $details) {

		// Get timestamp
		$date = time();

		// Get IP address
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}

		// SQL Query
		$sql = "INSERT INTO tbl_eventlog (ltimestamp, lipaddress, luser, leventtype, ldetails) VALUES ('" . $date . "', '" . $ip . "', '" . $user . "', '" . $eventtype . "', '" . $details . "')";

		// Check for errors
		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}

	}

	// Returns paginated data
	function paginate($sql, $page, $selectors) {

		// Get total rows
		$allrows = $this->dbc->query($sql);
		$totalrows = $allrows->num_rows;

		// Calculate start row
		$rows = 20;
		$startrow = ($rows * $page) - $rows;
		$numrows = $rows;

		// Calculate pages
		$pages = ceil($totalrows / $rows);

		$sql = $sql . ' limit ' . $startrow . ', ' . $numrows;

		if (!$this->result = $this->dbc->query($sql)) {
			exit ('<strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql);
		}

		// Get results
		$rows = array();
		while ($row = $this->result->fetch_assoc()) {
			$results[] = $row;
		}

		$rows['results'] = $results;
		$rows['total'] = $allrows->num_rows;
		$rows['pagenumber'] = 'Page ' . $page . ' of ' . $pages;


		// Page button settings
		$p = 1;
		$showbuttons = 9;
		$middlebutton = 5;
		$startmoving = $middlebutton + 1;
		$stopmoving = $pages - $middlebutton;
		
		// Build page array
		while ($p <= $pages) {
			$pagenos[] = $p;
			$p++;
		}

		// Set buttons to show
		if ($pages > $showbuttons) {
			if ($page < $startmoving) {
				$pagearray = array_slice($pagenos, 0 , $showbuttons);
			} else if ($page > $stopmoving) {
				$pagearray = array_slice($pagenos, ($pages - $showbuttons), $showbuttons);
			} else {
				$pagearray = array_slice($pagenos, ($page - $middlebutton), $showbuttons);
			}
		} else {
			$pagearray = $pagenos;
		}

		// Create page buttons
		$rows['pagebuttons'] = '<ul class="pagination">';
		
		if ($pages > $showbuttons) {
			if ($page == 1) {
				$rows['pagebuttons'] .= '<li class="disabled"><a href="#" style="width: 40px; text-align: center;"><i class="fa fa-angle-double-left"></i></a></li>';
			} else {
				$rows['pagebuttons'] .= '<li><a href="?page=1' . $selectors . '" style="width: 40px; text-align: center;"><i class="fa fa-angle-double-left"></i></a></li>';
			}

			if ($page == 1) {
				$rows['pagebuttons'] .= '<li class="disabled"><a href="#" style="width: 40px; text-align: center;"><i class="fa fa-angle-left"></i></a></li>';
			} else {
				$rows['pagebuttons'] .= '<li><a href="?page=' . ($page - 1) . '' . $selectors . '" style="width: 40px; text-align: center;"><i class="fa fa-angle-left"></i></a></li>';
			}
		}

		foreach($pagearray as $btn) {
			if ($btn == $page) {
				$rows['pagebuttons'] .= '<li class="active"><a href="?page=' . $btn . '' . $selectors . '" style="width: 40px; text-align: center;">' . $btn . '</a></li>';				
			} else {
				$rows['pagebuttons'] .= '<li><a href="?page=' . $btn . '' . $selectors . '" style="width: 40px; text-align: center;">' . $btn . '</a></li>';
			}
		}
		
		if ($pages > $showbuttons) {
			if ($page == $pages) {
				$rows['pagebuttons'] .= '<li class="disabled"><a href="#" style="width: 40px; text-align: center;"><i class="fa fa-angle-right"></i></a></li>';
			} else {
				$rows['pagebuttons'] .= '<li><a href="?page=' . ($page + 1) . '' . $selectors . '" style="width: 40px; text-align: center;"><i class="fa fa-angle-right"></i></a></li>';
			}

			if ($page == $pages) {
				$rows['pagebuttons'] .= '<li class="disabled"><a href="#" style="width: 40px; text-align: center;"><i class="fa fa-angle-double-right"></i></a></li>';
			} else {
				$rows['pagebuttons'] .= '<li><a href="?page=' . $pages . '' . $selectors . '" style="width: 40px; text-align: center;"><i class="fa fa-angle-double-right"></i></a></li>';
			}
		}

		$rows['pagebuttons'] .= '</ul>';

		$this->result->close();
		return $rows;
	}
	
	
	function Run($query) {
		$sql = $query;
		
		if (!$this->result = $this->dbc->query($sql)) {
			echo '<div class="alert alert-error"><strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql . '</div>';
		}
	}
	
	function insertQuery($query) {
		$sql = $query;
		
		if (!$this->result = $this->dbc->query($sql)) {
			echo '<div class="alert alert-error"><strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql . '</div>';
		}
	}
	
	
	function emptyTable($table) {
		$sql = "TRUNCATE " . $table;
		
		if (!$this->result = $this->dbc->query($sql)) {
			echo '<div class="alert alert-error"><strong>Error: </strong> Query Failed: (' . $this->dbc->errno . ') ' . $this->dbc->error . ' -- Query: ' . $sql . '</div>';
		}
	}
 
}

?>