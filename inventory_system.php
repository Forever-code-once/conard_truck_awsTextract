<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Inventory System" ?>
<?    
    $use_bootstrap = true;

    //Category
    function mrr_inv_cat_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_cats where deleted = 0 order by inv_cat_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['inv_cat_name']);
              if($cd==1)    $label=trim($row['inv_cat_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    //Manufacturers
    function mrr_inv_manu_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_manu where deleted = 0 order by manu_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['manu_name']);
              if($cd==1)    $label=trim($row['manu_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    //Vendors
    function mrr_inv_vendor_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_vendors where deleted = 0 order by vendor_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['vendor_name']);
              if($cd==1)    $label=trim($row['vendor_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    //Parts
    function mrr_inv_parts_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_parts where deleted = 0 order by inv_part_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['inv_part_name']);
              if($cd==1)    $label=trim($row['inv_part_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }

    if(!isset($_POST['vendor_id']))     $_POST['vendor_id']=0;
    if(!isset($_POST['manu_id']))       $_POST['manu_id']=0;
    if(!isset($_POST['cat_id']))        $_POST['cat_id']=0;
    if(!isset($_POST['part_id']))       $_POST['part_id']=0;
?>
<? include('header.php') ?>
<?php
    //general list section....
    $use_name_vs_code="_name";     
    //$use_name_vs_code="_code";
    $sql = "
        select inventory_parts.*,
            (select vendor".$use_name_vs_code." from inventory_vendors where inventory_vendors.id =inventory_parts.vendor_id) as part_vendor,
            (select manu".$use_name_vs_code." from inventory_manu where inventory_manu.id=inventory_parts.manu_id) as part_manu,
            (select inv_cat".$use_name_vs_code." from inventory_cats where inventory_cats.id=inventory_parts.cat_id) as part_cat
        from inventory_parts 
        where inventory_parts.deleted = 0 and inventory_parts.active > 0
            ".($_POST['vendor_id'] > 0 ? "and inventory_parts.vendor_id='".$_POST['vendor_id']."'" : "")."
            ".($_POST['manu_id'] > 0 ? "and inventory_parts.manu_id='".$_POST['manu_id']."'" : "")."
            ".($_POST['cat_id'] > 0 ? "and inventory_parts.cat_id='".$_POST['cat_id']."'" : "")."
            ".($_POST['part_id'] > 0 ? "and inventory_parts.id='".$_POST['part_id']."'" : "")."
        order by inventory_parts.inv_part_name asc, inventory_parts.id asc
    ";
    $data = simple_query($sql);

    $tot_qty_ordered=0;
    $tot_qty_received=0;
    $tot_qty_used=0;
    $tot_qty_left=0;
    
    $tot_cost_ordered=0;
    $tot_cost_received=0;
    $tot_cost_used=0;
    $tot_cost_left=0;
?>
<div class='container col-md-12'>
	<div class='col-md-12'>
		<div class="panel panel-info">
			<div class="panel-heading"><?=$usetitle ?></div>
			<div class="panel-body">
                <!----
                    <td><input name="sbox" class='form-control' value="<?=$_POST['sbox']?>"></td>
					<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-search"></span> Search</button></td>					
				----->
                <form action="<?=$SCRIPT_NAME?>" method="post">
                    <table class='table table-bordered well'>
                        <tr>
                            <td valign="top"><b>FILTERS:</b></td>
                            <td valign="top"><b>Vendor:</b> <?=mrr_inv_vendor_box("vendor_id",$_POST['vendor_id'],0," onChange='submit();'") ?></td>
                            <td valign="top"><b>Manufacturer:</b> <?=mrr_inv_manu_box("manu_id",$_POST['manu_id'],0," onChange='submit();'") ?></td>
                            <td valign="top"><b>Category:</b> <?=mrr_inv_cat_box("cat_id",$_POST['cat_id'],0," onChange='submit();'") ?></td>
                            <td valign="top"><b>Part:</b> <?=mrr_inv_parts_box("part_id",$_POST['part_id'],0," onChange='submit();'"); ?></td>
                        </tr>
                    </table>
                </form>

                <br><br>
                <b>Inventory Parts</b><br>
				<table class='table table-striped'>	
				<thead>	
          		<tr>
                    <!----
                    <th><b>ID</b></th>  
                    <th>&nbsp;</th>                    
                    ----->
                    <th><b>Vendor</b></th>
                    <th><b>Manu</b></th>
                    <th><b>Cat</b></th>        			
          			<th><b>Part <?=($use_name_vs_code=="_code" ? "Code" : "Name")?></b></th>
                    <th align="right"><b>Cost</b></th>
                    
                    <th align="right"><b>On Order</b></th>
                    <th align="right"><b>Value</b></th>
                    
                    <th align="right"><b>Received</b></th>
                    <th align="right"><b>Value</b></th>
                    
                    <th align="right"><b>Used</b></th>
                    <th align="right"><b>Value</b></th>
                    
          			<th align="right"><b>On Hand</b></th>
                    <th align="right"><b>Value</b></th>          			
          		</tr>
          		</thead>
          		<tbody>
          		<? while($row = mysqli_fetch_array($data)) { ?>  
                     <?php
                     //<td><?=$row['id']? ></td>
                     $qty_ordered=0;
                     $qty_received=0;
                     $qty_used=0;
                     $qty_on_hand=0;
                     
                     $qty_id=0;
                     
                     $sql2 = "
                        select inventory_log.*
                        from inventory_log 
                        where inventory_log.deleted = 0 and inventory_log.part_id='".$row['id']."'
                        order by inventory_log.linedate_added desc, inventory_log.id desc
                     ";
                     $data2 = simple_query($sql2);
                     if($row2 = mysqli_fetch_array($data2))
                     {   //capture the last used row for this part... assuming that we will add more than one per date or per cost change.
                         $qty_id=$row2['id'];
                         $qty_ordered=$row2['qty_ordered'];
                         $qty_received=$row2['qty_received'];
                         $qty_used=$row2['qty_used'];
     
                         $qty_on_hand=$qty_received - $qty_used;
                     }
                     else
                     {   //Create teh row for it if it does not yet exist...
                         $sql2 = "
                            insert into inventory_log
                                (id,linedate_added,deleted,part_id,vendor_id,manu_id,cat_id,user_id)
                            values 
                                (NULL,NOW(),0,'".$row['id']."','".$row['vendor_id']."','".$row['manu_id']."','".$row['cat_id']."','".$_SESSION['user_id']."')
                         ";
                         $data2 = simple_query($sql2);
                         $qty_id=mysqli_insert_id($datasource);                        
                     }
                     $cost_val=(double) $row['cost'];
                     $val_ordered=$cost_val * (double) $qty_ordered;
                     $val_received=$cost_val * (double) $qty_received;
                     $val_used=$cost_val * (double) $qty_used;
                     $val_on_hand=$cost_val * (double) $qty_on_hand;
           
                     $tot_qty_ordered+=$qty_ordered;
                     $tot_qty_received+=$qty_received;
                     $tot_qty_used+=$qty_used;
                     $tot_qty_left+=$qty_on_hand;
           
                     $tot_cost_ordered+=$val_ordered;
                     $tot_cost_received+=$val_received;
                     $tot_cost_used+=$val_used;
                     $tot_cost_left+=$val_on_hand;
                     ?>
          			<tr>
                        <td><?=$row['part_vendor']?></td>
                        <td><?=$row['part_manu']?></td>
                        <td><?=$row['part_cat']?></td>
                        <td><span title="<?=strip_tags($row['inv_part_desc'])?>"><?=$row['inv_part'.$use_name_vs_code.'']?></span></td>
                        <td align="right">$<?=number_format($row['cost'],2) ?><input type="hidden" id="cost_<?=$qty_id ?>" value="<?=$row['cost']?>"></td>
                        
                        <td align="right"><input id="ordered_<?=$qty_id ?>" value="<?=money_strip($qty_ordered)?>" style="text-align:right; padding:5px;" size="10" onBlur="mrr_update_inv_part(<?=$qty_id ?>,1);"></td>
                        <td align="right"><span id="ordered_val_<?=$qty_id ?>">$<?=number_format($val_ordered,2)?></span></td>

                        <td align="right"><input id="received_<?=$qty_id ?>" value="<?=money_strip($qty_received)?>" style="text-align:right; padding:5px;" size="10" onBlur="mrr_update_inv_part(<?=$qty_id ?>,2);"></td>
                        <td align="right"><span id="received_val_<?=$qty_id ?>">$<?=number_format($val_received,2)?></span></td>

                        <td align="right"><input id="used_<?=$qty_id ?>" value="<?=money_strip($qty_used)?>" style="text-align:right; padding:5px;" size="10" onBlur="mrr_update_inv_part(<?=$qty_id ?>,3);"></td>
                        <td align="right"><span id="used_val_<?=$qty_id ?>">$<?=number_format($val_used,2)?></span></td>

                        <td align="right"><i><span id="on_hand_<?=$qty_id ?>"><?=money_strip($qty_on_hand)?></span></i></td>
                        <td align="right"><i><span id="on_hand_val_<?=$qty_id ?>">$<?=number_format($val_on_hand,2)?></span></i></td>                              
          			</tr>
          		<? } ?>
                <tr>
                    <td colspan="5">Total</td>

                    <td align="right"><?=money_strip($tot_qty_ordered)?></td>
                    <td align="right">$<?=number_format($tot_cost_ordered,2)?></td>

                    <td align="right"><?=money_strip($tot_qty_received)?></td>
                    <td align="right"><?=number_format($tot_cost_received,2)?></td>

                    <td align="right"><?=money_strip($tot_qty_used)?></td>
                    <td align="right"><?=number_format($tot_cost_used,2)?></td>

                    <td align="right"><i><?=money_strip($tot_qty_left)?></i></td>
                    <td align="right"><i>$<?=number_format($tot_cost_left,2)?></i></td>
                </tr>                
          		</tbody>
          		</table>               
                
			</div>
		</div>
    </div>
</div>
<? include('footer.php') ?>
<script type='text/javascript'>
    function mrr_update_inv_part(qty_id,cd)
    {
        var valu=0;
        if(cd==1)       valu=$('#ordered_'+qty_id+'').val();
        if(cd==2)       valu=$('#received_'+qty_id+'').val();
        if(cd==3)       valu=$('#used_'+qty_id+'').val();

        $.ajax({
            type: "POST",
            url: "ajax.php?cmd=mrr_update_inv_part_qty",
            data: { "id": qty_id,
                "mode": cd,
                "value": parseInt(valu) },
            dataType: "xml",
            async:false,
            cache:false,
            success: function(xml) {
                if($(xml).find('rslt').text() == 0) {
                    $.prompt("Error saving the inventory part qty, please try again");
                } else {
                    $.noticeAdd({text: "Saved inventory part qty amount successfully."});

                    mrr_recalc_inv_part_tot(qty_id);
                }
            }
        });
    }
    function mrr_recalc_inv_part_tot(qty_id)
    {
        var valu1=$('#ordered_'+qty_id+'').val();
        var valu2=$('#received_'+qty_id+'').val();
        var valu3=$('#used_'+qty_id+'').val();
        var cost=parseFloat($('#cost_'+qty_id+'').val());
        
        o_qty_val=parseFloat(valu1) * cost;
        r_qty_val=parseFloat(valu2) * cost;
        u_qty_val=parseFloat(valu3) * cost;

        $('#ordered_val_'+qty_id+'').html(formatCurrency(o_qty_val));
        $('#received_val_'+qty_id+'').html(formatCurrency(r_qty_val));
        $('#used_val_'+qty_id+'').html(formatCurrency(u_qty_val));
        
        tot_qty=parseInt(valu2) - parseInt(valu3);
        tot_qty_val = (parseFloat(valu2) - parseFloat(valu3)) * cost;
        $('#on_hand_'+qty_id+'').html(tot_qty);
        $('#on_hand_val_'+qty_id+'').html(formatCurrency(tot_qty_val));
        
        $.noticeAdd({text: "Updated totals for inventory part."});
    }
</script>