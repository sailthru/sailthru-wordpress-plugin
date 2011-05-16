<div class="wrap">
	<h2>Sailthru Options</h2>

	<?php
		if(!isset($_GET['action']) || !in_array($_GET['action'], array('forms', 'options', 'blast', 'datafeeds'))) {
			$action = 'forms';
		}
		else {
			$action = $_GET['action'];
		}
		if($action == 'options') {
			?>
				<a href="options-general.php?page=sailthru&action=forms">Edit Forms</a> | <a href="options-general.php?page=sailthru&action=blast">Send an Email Blast</a> | General Options | <a href="options-general.php?page=sailthru&action=datafeeds">Manage Datafeeds</a>
				<br />
				<br />
				<form action="options.php" method="post">
					<?php wp_nonce_field('update-options'); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">API Key:</th>
							<td>
								<input type="text" name="sailthru_api_key" value="<?php echo get_option('sailthru_api_key'); ?>" style="width: 350px;" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Secret:</th>
							<td>
								<input type="text" name="sailthru_secret" value="<?php echo get_option('sailthru_secret'); ?>" style="width: 350px;" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Send Welcome Email</th>
							<td>
								<script type="text/javascript">
									jQuery(window).ready(function() {
										jQuery('#sailthru_welcome').click(function() {
											if (jQuery(this).is(':checked')) {
												jQuery('#sailthru_welcome_template').show();
											} else {
												jQuery('#sailthru_welcome_template').hide();
											}
										});
									});
								</script>
								<?php $visible = (bool)get_option('sailthru_welcome') ?>
								<input id="sailthru_welcome" type="checkbox" name="sailthru_welcome" value="1" <?php echo $visible ? ' checked="checked" ' : '' ?> />
								<select id="sailthru_welcome_template" name="sailthru_welcome_template" style="<?php echo $visible ? '' : 'display:none' ?>">
									<?php $templates = Sailthru::get_template_list(); ?>
									<?php $selected = get_option('sailthru_welcome_template'); ?>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo $template ?>"<?php echo $selected == $template ? ' selected' : '' ?>><?php echo $template ?></option>
                                    <?php endforeach ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Horizon Subdomain:</th>
							<td>
								<input type="text" name="horizon_subdomain" value="<?php echo get_option('horizon_subdomain'); ?>" style="width: 350px;" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" valign="top"><label for="all_email">Send all WordPress emails via Sailthru:</label></th>
							<td>
								<input type="checkbox" onclick="if(this.checked)alert('You must create a template called \'WordPress Template\' with subject \'{subject}\' and body \'{body}\' to use this feature.  You can create this template from the Sailthru control panel.');" id="all_email" name="sailthru_all_email" <?php echo get_option('sailthru_all_email') ? 'checked="checked"' : '' ?>>
							</td>
						</tr>
					</table>
					<p>
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="page_options" value="sailthru_api_key,sailthru_secret,sailthru_all_email,horizon_subdomain,sailthru_welcome,sailthru_welcome_template" />
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>
			<?php
		}
		elseif($action == 'blast') {
			if(count($_POST)) {
				$required_fields = array(
					'blast_name' => 'You must enter a Name.',
					'from_name'	=> 'You must enter a From Name.',
					'from_email' => 'You must enter a From Email.',
					// 'list'	=> 'You must select a list.',
					'schedule_time' => 'You must enter a Schedule Time',
					'subject' => 'You must enter a Subject',
					'content'=> 'You must enter a HTML Body.',
					'plaintext_body' => 'You must enter a Text Body.'
				);
				
				$errors = array();
				foreach($required_fields as $field => $msg) {
					if(!isset($_POST[$field]) || !$_POST[$field]) {
						$errors[] = $msg;
					}
				}
				
				if(!is_numeric($_POST['list'])) {
					$errors[] = 'You must select a list.';
				}
				
				if($_POST['schedule_time'] && (strtotime($_POST['schedule_time'])) == FALSE) {
					$errors[] = 'Invalid schedule time.';
				}

				if(!count($errors)) {
					
					$lists = sailthru_form::get_all_lists();
					$list = $lists[$_POST['list']];
					
					require_once('client/requires.php');
					$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
					
					$r = $client->scheduleBlast(
						$_POST['blast_name'],
						$list,
						$_POST['schedule_time'],
						$_POST['from_name'],
						$_POST['from_email'],
						$_POST['subject'],
						$_POST['content'],
						$_POST['plaintext_body'],
					    array()
					);

					if($r['status'] == 'scheduled') {
						$errors[] = 'Your blast has been successfully scheduled for: ' . $r['schedule_time'];
						update_option('triggermail_last_blast', $_POST);
					}
					else {
						$errors[] = $r['errormsg'];
					}

				}
				
			}
			else {
				//no post..
				$_POST = get_option('triggermail_last_blast');
			}
			?>
				<a href="options-general.php?page=sailthru&action=forms">Edit Forms</a> | Send an Email Blast | <a href="options-general.php?page=sailthru&action=options">General Options</a> | <a href="options-general.php?page=sailthru&action=datafeeds">Manage Datafeeds</a>
				<?php
					if(isset($errors) && is_array($errors) && count($errors)) {
						echo '<p style="color: red; font-weight: bold; padding: 0; margin: 0;">';
						foreach($errors as $error) {
							echo "<br /> {$error}";
						}
						echo '</p>';
					}
				?>
				
				<p>
					<form action="" method="post">
						<table class="widefat">
							<thead>
								<tr>
									<th scope="col" colspan="2" id="heading">Create Email Blast</th>
								</tr>
							</thead>
							<tr>
								<td style="border: 0px;">
									<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td style="border: 0px;">
											Load From Template: 
										</td>
										<td style="border: 0px;">
											<select name="template" id="template">
                                                <option value=""></option>
                                                <?php $templates = Sailthru::get_template_list(); ?>
                                                <?php foreach ($templates as $template): ?>
                                                    <option value="<?php echo $template ?>"><?php echo $template ?></option>
                                                <?php endforeach ?>
											</select>
											<input type="submit" id="get_template" class="button primary" value="Load"><img src="<?php echo Sailthru::get_plugin_url() ?>/loading.gif" id="template_spinner" style="display:none;" />
										</td>
									</tr>
									<tr>
										<td style="border: 0px;">
											Blast Name: 
										</td>
										<td style="border: 0px;">
											<input type="text" name="blast_name" style="width: 250px;" value="<?php echo $_POST['blast_name'] ?>">
										</td>
									</tr>
										<tr>
											<td style="border: 0px;">
												From Name: 
											</td>
											<td style="border: 0px;">
												<input type="text" id="from_name" name="from_name" style="width: 250px;" id="from_name" value="<?php echo $_POST['from_name'] ?>">
											</td>
										</tr>
										<tr>
											<td style="border: 0px;">
												From Email: 
											</td>
											<td style="border: 0px;">
												<input type="text" id="from_email" name="from_email" style="width: 250px;" value="<?php echo $_POST['from_email'] ?>">
											</td>
										</tr>
										<tr>
											<td style="border: 0px;">
												<nobr>Select List:</nobr>
											</td>
											<td style="border: 0px;">
												<select name="list" style="">
													<option value="">Select...</option>
													<?php
														if(!isset($lists)) {
															$lists = sailthru_form::get_all_lists();
														}
														foreach($lists as $id => $name) {
															echo "<option value=\"{$id}\"";
															if($_POST['list'] == $id) {
																echo ' selected';
															}
															echo ">{$name}</option>";
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td style="border: 0px;">
												Schedule Time: 
											</td>
											<td style="border: 0px;">
												<input type="text" name="schedule_time" style="width: 250px;" value="<?php echo $_POST['schedule_time'] ?>">
											</td>
										</tr>
										<tr>
											<td style="border: 0px;">
												Subject: 
											</td>
											<td style="border: 0px;">
												<input type="text" name="subject" id="subject" style="width: 650px;" value="<?php echo $_POST['subject'] ?>">
											</td>
										</tr>
										<tr style="display:none;" id="datafeed_tr">
											<td style="border: 0px;">
												Prefill Body From Data Feed
											</td>
											<td style="border: 0px;">
												<select id="datafeeds">
													<?php $datafeeds = get_option('sailthru_datafeeds'); ?>
													<?php if (is_array($datafeeds)): ?>
    													<?php foreach ($datafeeds as $name => $url): ?>
    														<option value="<?php echo $url ?>"><?php echo $name ?></option>
    													<?php endforeach ?>
													<?php endif ?>
												</select>
												<input type="submit" id="prefill" class="button primary" value="Prefill"><img src="<?php echo Sailthru::get_plugin_url() ?>/loading.gif" id="prefill_spinner" style="display:none;" />
											</td>
										<tr>
											<td style="border: 0px;">
												HTML Body:
											</td>
											<td style="border: 0px;">
												<?php
													wp_admin_css('thickbox');
													wp_print_scripts('jquery-ui-core');
													wp_print_scripts('jquery-ui-tabs');
													wp_print_scripts('post');
													wp_print_scripts('editor');
													add_thickbox();
													wp_print_scripts('media-upload');
													if (function_exists('wp_tiny_mce')) wp_tiny_mce();
												?>
												<div class="wrap">
													<div id="poststuff">
													  <div id="postdivrich">
														<?php $content = $_POST['content']; ?>
													    <?php the_editor($content, 'content', 'title', false, 2); ?>
													  </div>
													</div>
												</div>
												<br />
											</td>
										</tr>
										<tr>
											<td style="border: 0px;">
												<nobr>Text Body:</nobr>
											</td>
											<td style="border: 0px;">
												<textarea name="plaintext_body" id="text_body" rows="10" cols="90"><?php echo $_POST['plaintext_body']; ?></textarea>
											</td>
										</tr>
										<tr>
											<td style="border: 0px;" colspan="2">
												<input type="submit" class="button primary" value="Send">
											</td>
										</tr>
									</table>
								</td>
								<td style="border: 0px;">
									<p>
										Email <a href="mailto:support@sailthru.com">support@sailthru.com</a> for any help you may need!
									</p>
								</td>
							</tr>
						</table>					
					</form>
				</p>
			<?php
		}
		elseif($action == 'forms') {
			
			$sailthru_forms = sailthru_form::get_forms();
			
			?>
				Edit Forms | <a href="options-general.php?page=sailthru&action=blast">Send an Email Blast</a>| <a href="options-general.php?page=sailthru&action=options">General Options</a> | <a href="options-general.php?page=sailthru&action=datafeeds">Manage Datafeeds</a>
			<?php
			
			if(isset($_GET['form_id']) && $_GET['form_id']) {
				$form_id = $_GET['form_id'] == 'new' ? false : $_GET['form_id'];
			}
			else {
				$form_id = false;
			}
			
			try {
				$form = new sailthru_form($form_id);
			}
			catch(Exception $e) {
				echo '<br />';
				echo '<span style="color: red; font-weight: bold;">';
				echo "Error: {$e->getMessage()}";
				echo '</span>';
			}
			if(isset($_POST['form_content'])) {
				$errors = array();
				
				if(!isset($_POST['form_name']) || !strlen($_POST['form_name'])) {
					$errors[] = 'You must provide a name for the form.';
				}
				
				if(!preg_match_all('/\[email[^\]]*[\]]/i', $_POST['form_content'], $matches)) {
					$errors[] = 'You must include an email field in your form.';
				}
				
				if(!preg_match_all('/\[submit[^\]]+[\]]/i', $_POST['form_content'], $matches)) {
					$errors[] = 'You must include a submit button with valid button text.';
				}
				
				if(!isset($_POST['lists']) || !is_array($_POST['lists']) || !count($_POST['lists'])) {
					$errors[] = 'You must select at least one list.';
				}
				
				if(!count($errors)) {
					$form->name = $_POST['form_name'];
					$form->mailing_lists = $_POST['lists'];
					$form->content = stripslashes($_POST['form_content']);
					$sailthru_forms = $form->save();
					$form_id = $form->id;
					$saved = true;
					
					if(isset($_GET['form_id']) && $_GET['form_id'] == 'new') {
						header('Location: options-general.php?page=sailthru&form_id=' . $form->id);
					}
				}
				else {
					$saved = false;
				}
			}
			elseif(isset($_POST['delete_form']) && $_POST['delete_form']) {;
				$sailthru_forms = sailthru_form::delete_form($_POST['delete_form']);
			}
			
			?>
			
			<p>
				<form action="" method="get" id="change_form">
					Create a new form below, or select one to edit: 
					<select name="form_id" id="edit_forms" class="widefat" style="width: auto; vertical-align: middle;" onchange="if(jQuery(this).val()) jQuery('#change_form').submit();">
						<option value="">Select...</option>
						<?php
							if($form_id) {
								echo '<option value="new">New Form</option>';
							}
							if(is_array($sailthru_forms)) {
								foreach($sailthru_forms as $id => $f) {
									echo "<option value='{$id}'";
									if($form_id == $id) {
										echo ' selected';
									}
									echo ">{$f->name}</option>";
								}
							}
						?>
					</select>
					<?php
						if($saved) {
							echo '<p style="color: red; font-weight: bold; padding: 0; margin: 0;">';
							echo '<br />Your form has been saved.';
							echo '</p>';
						}
						elseif(isset($errors) && is_array($errors) && count($errors)) {
							echo '<p style="color: red; font-weight: bold; padding: 0; margin: 0;">';
							foreach($errors as $error) {
								echo "<br /> {$error}";
							}
							echo '</p>';
						}
					?>
					<input type="hidden" name="page" value="sailthru">
				</form>
			</p>
			<br />
			<form action="" method="post">
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" colspan="2" id="heading">Create New Form</th>
						</tr>
					</thead>
					<tr>
						<td style="width: 500px;">
							<p style="margin-left: 7px;">Form Name:  <input type="text" name="form_name" style="width: 300px;" value="
								<?php
									if(isset($errors) && count($errors)) {
										echo $_POST['form_name'];
									}
									else {
										echo $form->name;
									}
								?>	
							"></p>
							<table border="0">
								<tr>
									<td colspan="4" style="border: 0px;">
										Select Lists:
									</td>
								</tr>
								<?php
									$lists = sailthru_form::get_all_lists();
									$i = 0;
									foreach($lists as $id => $list) {
										if($i % 4 == 0) {
											echo '<tr>';
										}
										echo '<td style="border: 0px;">';
											echo "<input type='checkbox' name='lists[]' value='{$id}' id='list-{$id}'";
											if(isset($errors) && count($errors)) {
												if(@in_array($id, $_POST['lists'])) {
													echo ' checked="checked"';
												}
											}
											elseif(@in_array($id, $form->mailing_lists)) {
												echo ' checked="checked"';
											}
											echo ">&nbsp;<label for='list-{$id}' id='list_label-{$id}'>{$list}</label>";
										echo '</td>';
										if($i % 4 == 3 || $i == count($lists)) {
											echo '</tr>';
										}
										$i++;
									}
								?>
							</table>
							<p style="margin-left: 7px;">
								Form Layout:
							</p>
							
<textarea id="formcontent" rows="30" cols="60" name="form_content"><?php if(isset($errors) && count($errors)) { echo stripslashes($_POST['form_content']); } elseif($form->content) { echo stripslashes($form->content); } else { ?>
<div style="text-align: left;">
	<h4>Join My Mailing List</h4>
	<p>
		Email:
		<br />
		[email "class"]
	</p>
	<p>
		First name (required):
		<br />
		[fname* "another class"] 
	</p>
	<p>
		Last name:
		<br />
		[lname] 
	</p>
	<p>
		[submit "Subscribe" "class"]
	</p>
</div><?php } ?>
</textarea>
						</td>
						<td>
							<div style="width: 91%; height: 100%; padding: 3px;">
								<p style="padding: 3px; margin: 3px;">
									Select the lists on the left to which this form will subscribe your users.  For each list you may include a checkbox in the form which will allow users to explicitly opt-in to that particular list.  If you do not include a checkbox for any given list, all users will automatically be subscribed to it.
								</p>
								<p style="padding: 3px; margin: 3px;">
									Design your form using HTML in the box on the left.  You are required to include an email field, however, the others are optional.
								</p>
								<p style="padding: 3px; margin: 3px;">
									You may wish to define CSS classes to add styling to your form fields.  To do so, include the class name inside of quotes in the square brackets (as in the "Email" and "Last Name" examples below).  The CSS classes you include here must be defined in your theme's CSS files.  Including a class is optional for all fields.
								</p>
								<p style="padding: 3px; margin: 3px;">
									The submit button requires text to be displayed inside of it (such as "Subscribe").  Include this text in quotes inside of the square brackets.  You may optionally include a CSS class after the button text (see example below).
								</p>
								<p style="padding: 3px; margin: 3px;">
									Email will be required by default when users are completing the form.  You may determine if any of the other fields are optional or required.  To make a field required, place an asterisk  ("*") inside the brackets (as in the "First Name" example below).
								</p>
								<table id="fields">
									<thead>
										<tr>
											<td colspan="2">
												<strong>Available Fields:</strong>
											</td>
										</tr>
									</thead>
									<tr>
										<td>
											Email:
										</td>
										<td>
											[email "class"]
										</td>
									</tr>
									<tr>
										<td>
											First Name:
										</td>
										<td>
											[fname* "another class"]
										</td>
									</tr>
									<tr>
										<td>
											Last Name:
										</td>
										<td>
											[lname]
										</td>
									</tr>
									<tr>
										<td>
											Submit Button:
										</td>
										<td>
											[submit "Subscribe" "class"]
										</td>
									</tr>
								</table>
								<p style="padding: 3px; margin: 3px;">
									<br />
									Include this form anywhere on your blog by including the code below.  This code will be replaced with the form you designed on the left.
									<br />
									<br />
									<strong>Code:</strong>&nbsp;&nbsp;
									<?php
										echo $form->get_form_code();
									?>
								</p>
							</div>
							<br />
								<button class="button-primary">Save Form</button>
								</form>
								<?php
									if($form_id) {
										?>
											<form style="display: inline;" action="options-general.php?page=sailthru" method="post">
												<button class="button-primary">Delete Form</button>
												<input type="hidden" name="delete_form" value="<?php echo $form_id; ?>">
											</form>
										<?php
									}
								?>
						</td>
					</tr>
				</table>
			<?php
		}
		elseif ($action == 'datafeeds') {
			?> 
			<a href="options-general.php?page=sailthru&action=forms">Edit Forms</a> | <a href="options-general.php?page=sailthru&action=blast">Send an Email Blast</a>| <a href="options-general.php?page=sailthru&action=options">General Options</a> | Manage Datafeeds
			<br />
			<br />
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Data Feeds:</th>
					<td>
						<select id="datafeeds">
							<option value=""></option>
							<option value="new">Add New</option>
							<?php $datafeeds = get_option('sailthru_datafeeds') ?>
							<?php if ($datafeeds): ?>
								<?php foreach ($datafeeds as $name => $url): ?>
									<option value="<?php echo $url ?>"><?php echo $name ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Name:</th>
					<td>
						<input type="text" name="name" id="name" value="" style="width: 350px;" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">URL:</th>
					<td>
						<input type="text" name="url" id="url" value="" style="width: 350px;" />
					</td>
				</tr>
			</table>
			<p>
				<input id="save_datafeeds" type="submit" class="button-primary" value="<?php _e('Save') ?>" /> <input id="delete_datafeed" type="submit" class="button-primary" value="<?php _e('Delete') ?>" /> <img src="<?php echo Sailthru::get_plugin_url() ?>/loading.gif" id="spinner" style="display:none;" />
			</p>
		<?php			
		}
	?>
</div>