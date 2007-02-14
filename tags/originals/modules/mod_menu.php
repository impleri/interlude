<?php
global $style;
$module =  $style->_here;
?>

<table style="width:200px;">
<tr>
<td>Mod: <?php echo $module['sm_id'];?></td>
</tr>
<tr>
<td>Row: <?php echo $row['sm_id'];?></td>
</tr>
<tr>
<td>Sty: <?php echo $style->_here['sm_id'];?></td>
</tr>
</table>