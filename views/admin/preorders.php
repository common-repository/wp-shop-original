<style type="text/css"> 
  th,td {
    border-bottom: 1px solid #ddd;
    padding: 15px;
    text-align: center;
  }
</style>
<script>
jQuery(function() {
	jQuery('.moderate_input').change(function(e){
		e.preventDefault();
    var sess =jQuery(this).data('sess');
    var val = jQuery(this).attr('checked');
		jQuery.ajax({
			type: "POST",
			url: "<?php echo $this->url;?>/wp-admin/admin-ajax.php?action=moderate_cart",
			data: {action:'moderate_cart',session:sess,value:val},
			success: function(t){
				
			}
		});
	});
});
</script>


<div class="wrap">
<div id="poststuff">
	<div class="postbox">
   <div>
<?php
  $pre_orders = Wpshop_Orders::getCartPreOrders();
  if(is_array($pre_orders)):
    
    echo '<table style="width:100%" cellpadding="2" cellspacing="2" >';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Date</th>';
    echo '<th>Login</th>';
    echo '<th>Link</th>';
    echo '<th>Moderated</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $link = '';
    $login = '';
    foreach($pre_orders as $pre_order):
      echo '<tr>';
      $customers = Wpshop_Orders::getCartUser($pre_order['session']);
      $cust_admin = 0;
      $cust_guest = 0;
      $cust_user = 0;

      if(is_array($customers)):
        foreach($customers as $cust):
          if(isset($cust['user'])&&(int)$cust['user']>0&&$cust['user']!=1):
            $cust_user = $cust['user'];
            break;
          elseif(isset($cust['user'])&&$cust['user']==0):
            $cust_guest = 1;
          elseif(isset($cust['user'])&&$cust['user']==1):
            $cust_admin = 1;
          endif;
        endforeach;
      endif;     

      if($cust_user>0):
        $user_info = get_userdata($cust_user);
        $login = $user_info->user_login;
        $link = "/wp-admin/user-edit.php?user_id=".$user_info->ID;
      elseif($cust_guest==1):
        $login = 'Guest';
        $link ='';
      else:
        $login = 'Admin';
        $user_info = get_userdata($cust_user);
        $link = '';
      endif;

      $moderated = Wpshop_Orders::getmetkaPreOrders($pre_order['session']);
      if(isset($moderated[0]['id'])&&(int)$moderated[0]['id']>0):
        $checked = 'checked';
      else:
        $checked = '';
      endif;

      //echo '<td>sess: '.$pre_order['session'].'</td>';
      echo '<td>'.$pre_order['date'].'</td>';
      if($link&&$link!=''):
        echo '<td><a href="'.$link.'">'.$login.'</a></td>';
      else:
        echo '<td>'.$login.'</td>';
      endif;
      echo '<td><a href="'.$this->cart.'?step=3&moderate=true&secret='.$pre_order['session'].'" target="_blank">'.$this->cart.'?step=3&moderate=true&secret='.$pre_order['session'].'</a></td>';
      echo '<td><p><input type="checkbox" class="moderate_input" name="moderate" data-sess="'.$pre_order['session'].'" '.$checked.' /></p></td>';
      echo '</tr>';      
    endforeach;
    echo '</tbody>';
    echo '</table>';
  endif;
?>
</div>
</div>
</div>
</div>