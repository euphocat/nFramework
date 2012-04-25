<?php
/**
 * Interface permettant de comparer 2 objets de même type selon des critère définis
 * 
 * @author Nicolas Baptiste
 *
 */
Interface IComparable{
	/**
	 * @return bool
	 */
	public function compare(self $oToCompare);
}
?>