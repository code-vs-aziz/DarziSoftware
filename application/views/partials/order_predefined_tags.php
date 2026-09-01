<?php
$remark_field = isset($remark_field) ? (string) $remark_field : 'Remarks0';
$order_predefined_by_row = isset($order_predefined_by_row) ? $order_predefined_by_row : array();
$row_groups = array(1, 2);
$has_tags = FALSE;
foreach ($row_groups as $row_group)
{
	if ( ! empty($order_predefined_by_row[$row_group]))
	{
		$has_tags = TRUE;
		break;
	}
}
if ( ! $has_tags)
{
	return;
}
?>
<div class="order-predefined-tags" dir="rtl" lang="ur">
<?php
foreach ($row_groups as $row_group)
{
	if (empty($order_predefined_by_row[$row_group]))
	{
		continue;
	}
	$labels = array();
	foreach ($order_predefined_by_row[$row_group] as $item)
	{
		$label = isset($item['label_text']) ? trim((string) $item['label_text']) : '';
		$insert = isset($item['insert_text']) ? trim((string) $item['insert_text']) : $label;
		if ($label === '')
		{
			continue;
		}
		if ($insert === '')
		{
			$insert = $label.' -';
		}
		$labels[] = array('label' => $label, 'insert' => $insert);
	}
	if (empty($labels))
	{
		continue;
	}
?>
	<div class="order-predefined-row">
<?php
	$last = count($labels) - 1;
	foreach ($labels as $idx => $tag)
	{
?>
		<button type="button" class="order-predefined-tag" data-target="<?php echo esc_html($remark_field); ?>" data-insert="<?php echo esc_html($tag['insert']); ?>"><?php echo esc_html($tag['label']); ?></button><?php if ($idx < $last) { ?><span class="order-predefined-sep">,&nbsp;</span><?php } ?>
<?php
	}
?>
	</div>
<?php
}
?>
</div>
