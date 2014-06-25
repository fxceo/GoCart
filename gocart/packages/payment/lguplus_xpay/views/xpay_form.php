<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<label><?php echo lang('enabled');?></label>
<select name="enabled" class="span3">
	<option value="1"<?php echo((bool)$settings['enabled'])?' selected="selected"':'';?>><?php echo lang('enabled');?></option>
	<option value="0"<?php echo((bool)$settings['enabled'])?'':' selected="selected"';?>><?php echo lang('disabled');?></option>
</select>

<label><?php echo lang('test_mode_label') ?></label>
<select name="cst_platform" class="span3">
	<option value="test"<?php echo($settings['cst_platform'] == "test")?' selected="selected"':'';?>><?php echo lang('test_mode');?></option>
	<option value="service"<?php echo($settings['cst_platform'] == "service")?' selected="selected"':'';?>><?php echo lang('live_mode');?></option>
</select>

<label><?php echo lang('mid') ?></label>
<input class="span3" name="cst_mid" type="text" value="<?php echo @$settings["cst_mid"] ?>" size="50" >

<label><?php echo lang('mert_key') ?></label>
<input class="span3" name="mert_key" type="text" value="<?php echo @$settings["mert_key"] ?>" size="50">
