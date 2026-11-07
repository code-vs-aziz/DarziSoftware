<?php
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$table_ready = !empty($table_ready);
$settings = isset($settings) ? $settings : array();
$counts = isset($counts) ? $counts : array();
$active_tab = isset($active_tab) ? $active_tab : 'overview';
$base = site_url('Website');
?>
<body class="animsition erp-app erp-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('menu_website'); ?></h2>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<?php
								$web_suss = app_take_flash('suss');
								$web_ok = app_take_flash('success_msg');
								$web_err = app_take_flash('error');
								$web_flash_text = ($web_ok !== NULL && $web_ok !== '') ? (string) $web_ok : (($web_suss !== NULL && $web_suss !== '' && $web_suss !== 'yes') ? (string) $web_suss : '');
								if ($web_flash_text !== '') {
								?>
									<div class="alert alert-success"><?php echo esc_html($web_flash_text); ?></div>
								<?php } elseif ($web_suss === 'yes') { ?>
									<div class="alert alert-success"><?php echo esc_html(t('app_saved_success')); ?></div>
								<?php } ?>
								<?php if ($web_err !== NULL && $web_err !== '') { ?>
									<div class="alert alert-danger"><?php echo esc_html((string) $web_err); ?></div>
								<?php } ?>

								<?php if ( ! $table_ready) { ?>
									<div class="alert alert-warning">
										Run <code>application/sql/website_schema.sql</code> on the ERP database, then refresh.
									</div>
								<?php } else { ?>

								<nav class="mb-3">
									<div class="nav nav-tabs">
										<a class="nav-item nav-link<?php echo $active_tab === 'overview' ? ' active' : ''; ?>" href="<?php echo esc_attr($base); ?>">Overview</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'settings' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/settings')); ?>">Content</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'guides' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/guides')); ?>">Tutorials</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'submissions' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/submissions')); ?>">Measurements</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'categories' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/categories')); ?>">Categories</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'products' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/products')); ?>">Products</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'orders' ? ' active' : ''; ?>" href="<?php echo esc_attr(site_url('Website/orders')); ?>">Orders</a>
									</div>
								</nav>

								<?php if ($active_tab === 'overview') { ?>
									<div class="row">
										<div class="col-md-4 mb-3">
											<div class="dz-card p-3">
												<div class="text-muted">Pending measurement submissions</div>
												<div class="h3 mb-0"><?php echo (int) (isset($counts['pending_submissions']) ? $counts['pending_submissions'] : 0); ?></div>
											</div>
										</div>
										<div class="col-md-4 mb-3">
											<div class="dz-card p-3">
												<div class="text-muted">Pending website orders</div>
												<div class="h3 mb-0"><?php echo (int) (isset($counts['web_orders_pending']) ? $counts['web_orders_pending'] : 0); ?></div>
											</div>
										</div>
										<div class="col-md-4 mb-3">
											<div class="dz-card p-3">
												<div class="text-muted">Active products</div>
												<div class="h3 mb-0"><?php echo (int) (isset($counts['products_active']) ? $counts['products_active'] : 0); ?></div>
											</div>
										</div>
									</div>
									<p class="text-muted mb-0">Public site talks to this ERP via <code>/api/*</code>. Customers and measurements stay in the existing tables.</p>
								<?php } ?>

								<?php if ($active_tab === 'settings') { ?>
									<form method="post" action="<?php echo esc_attr(site_url('Website/save_settings')); ?>">
										<?php $this->load->view('partials/csrf_field'); ?>
										<div class="form-row">
											<div class="form-group col-md-6">
												<label>Site name</label>
												<input class="form-control" name="site_name" value="<?php echo esc_attr($settings['site_name']); ?>">
											</div>
											<div class="form-group col-md-6">
												<label>SEO title</label>
												<input class="form-control" name="seo_title_default" value="<?php echo esc_attr($settings['seo_title_default']); ?>">
											</div>
											<div class="form-group col-md-6">
												<label>Tagline (EN)</label>
												<input class="form-control" name="tagline_en" value="<?php echo esc_attr($settings['tagline_en']); ?>">
											</div>
											<div class="form-group col-md-6">
												<label>Tagline (UR)</label>
												<input class="form-control" name="tagline_ur" value="<?php echo esc_attr($settings['tagline_ur']); ?>" dir="rtl">
											</div>
											<div class="form-group col-md-12">
												<label>About (EN)</label>
												<textarea class="form-control" name="about_en" rows="3"><?php echo esc_html($settings['about_en']); ?></textarea>
											</div>
											<div class="form-group col-md-12">
												<label>About (UR)</label>
												<textarea class="form-control" name="about_ur" rows="3" dir="rtl"><?php echo esc_html($settings['about_ur']); ?></textarea>
											</div>
											<div class="form-group col-md-4">
												<label>Contact phone</label>
												<input class="form-control" name="contact_phone" value="<?php echo esc_attr($settings['contact_phone']); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>WhatsApp</label>
												<input class="form-control" name="contact_whatsapp" value="<?php echo esc_attr($settings['contact_whatsapp']); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>Email</label>
												<input class="form-control" name="contact_email" value="<?php echo esc_attr($settings['contact_email']); ?>">
											</div>
											<div class="form-group col-md-12">
												<label>SEO description</label>
												<textarea class="form-control" name="seo_description_default" rows="2"><?php echo esc_html($settings['seo_description_default']); ?></textarea>
											</div>
											<div class="form-group col-md-6">
												<label>OG image URL</label>
												<input class="form-control" name="og_image_url" value="<?php echo esc_attr($settings['og_image_url']); ?>">
											</div>
											<div class="form-group col-md-3">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="feature_measurements" value="1" <?php echo !empty($settings['feature_measurements']) ? 'checked' : ''; ?>> Measurements portal</label>
											</div>
											<div class="form-group col-md-3">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="feature_shop" value="1" <?php echo !empty($settings['feature_shop']) ? 'checked' : ''; ?>> Shop</label>
											</div>
										</div>
										<button type="submit" class="btn btn-primary">Save content</button>
									</form>
								<?php } ?>

								<?php if ($active_tab === 'submissions') {
									$submissions = isset($submissions) ? $submissions : array();
									$filter_status = isset($filter_status) ? $filter_status : 'pending';
								?>
									<form class="form-inline mb-3" method="get" action="<?php echo esc_attr(site_url('Website/submissions')); ?>">
										<select name="status" class="form-control mr-2">
											<?php foreach (array('pending','approved','rejected','all') as $st) { ?>
												<option value="<?php echo $st; ?>" <?php echo $filter_status === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
											<?php } ?>
										</select>
										<button class="btn btn-secondary" type="submit">Filter</button>
									</form>
									<div class="table-responsive">
										<table class="table table-sm table-striped">
											<thead>
												<tr>
													<th>ID</th>
													<th>Naap</th>
													<th>Customer</th>
													<th>Type</th>
													<th>Status</th>
													<th>Created</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												<?php if ( ! $submissions) { ?>
													<tr><td colspan="7" class="text-muted">No submissions.</td></tr>
												<?php } ?>
												<?php foreach ($submissions as $s) { ?>
													<tr>
														<td><?php echo (int) $s['id']; ?></td>
														<td><?php echo esc_html($s['man_napno']); ?></td>
														<td><?php echo esc_html($s['customer_name']); ?></td>
														<td><?php echo esc_html($s['submission_type']); ?></td>
														<td><?php echo esc_html($s['status']); ?></td>
														<td><?php echo esc_html($s['created_at']); ?></td>
														<td><a href="<?php echo esc_attr(site_url('Website/submission_view/'.$s['id'])); ?>">Review</a></td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>

								<?php if ($active_tab === 'categories') {
									$categories = isset($categories) ? $categories : array();
									$edit_category = isset($edit_category) && is_array($edit_category) ? $edit_category : NULL;
									$is_cat_edit = ! empty($edit_category);
									$ec = $is_cat_edit ? $edit_category : array();
								?>
									<form method="post" action="<?php echo esc_attr(site_url('Website/category_save')); ?>" class="mb-4 border rounded p-3">
										<?php $this->load->view('partials/csrf_field'); ?>
										<input type="hidden" name="id" value="<?php echo $is_cat_edit ? (int) $ec['id'] : 0; ?>">
										<div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
											<h5 class="mb-0"><?php echo $is_cat_edit ? 'Edit category #'.(int) $ec['id'] : 'Add category'; ?></h5>
											<?php if ($is_cat_edit) { ?>
												<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_attr(site_url('Website/categories')); ?>">Cancel edit</a>
											<?php } ?>
										</div>
										<div class="form-row">
											<div class="form-group col-md-4">
												<label>Name (EN)</label>
												<input class="form-control" name="name_en" required value="<?php echo esc_attr(isset($ec['name_en']) ? $ec['name_en'] : ''); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>Name (UR)</label>
												<input class="form-control" name="name_ur" dir="rtl" value="<?php echo esc_attr(isset($ec['name_ur']) ? $ec['name_ur'] : ''); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>Slug <small class="text-muted">(optional — auto from EN name)</small></label>
												<input class="form-control" name="slug" placeholder="e.g. kids-suits" value="<?php echo esc_attr(isset($ec['slug']) ? $ec['slug'] : ''); ?>">
											</div>
											<div class="form-group col-md-6">
												<label>Image URL</label>
												<input class="form-control" name="image_url" value="<?php echo esc_attr(isset($ec['image_url']) ? $ec['image_url'] : ''); ?>">
											</div>
											<div class="form-group col-md-3">
												<label>Sort order</label>
												<input class="form-control" name="sort_order" type="number" value="<?php echo esc_attr(isset($ec['sort_order']) ? $ec['sort_order'] : '100'); ?>">
											</div>
											<div class="form-group col-md-3">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="is_active" value="1" <?php echo (!$is_cat_edit || !empty($ec['is_active'])) ? 'checked' : ''; ?>> Active</label>
											</div>
											<div class="form-group col-md-6">
												<label>Description (EN)</label>
												<textarea class="form-control" name="description_en" rows="2"><?php echo esc_html(isset($ec['description_en']) ? $ec['description_en'] : ''); ?></textarea>
											</div>
											<div class="form-group col-md-6">
												<label>Description (UR)</label>
												<textarea class="form-control" name="description_ur" rows="2" dir="rtl"><?php echo esc_html(isset($ec['description_ur']) ? $ec['description_ur'] : ''); ?></textarea>
											</div>
										</div>
										<button class="btn btn-primary" type="submit"><?php echo $is_cat_edit ? 'Update category' : 'Save category'; ?></button>
									</form>
									<div class="table-responsive">
										<table class="table table-sm table-striped">
											<thead>
												<tr>
													<th>ID</th>
													<th>Name (EN)</th>
													<th>Name (UR)</th>
													<th>Slug</th>
													<th>Sort</th>
													<th>Active</th>
													<th style="min-width:180px">Actions</th>
												</tr>
											</thead>
											<tbody>
												<?php if ( ! $categories) { ?>
													<tr><td colspan="7" class="text-muted">No categories yet. Add one above.</td></tr>
												<?php } ?>
												<?php foreach ($categories as $cat) {
													$active = ! empty($cat['is_active']);
												?>
													<tr>
														<td><?php echo (int) $cat['id']; ?></td>
														<td><?php echo esc_html($cat['name_en']); ?></td>
														<td dir="rtl"><?php echo esc_html($cat['name_ur']); ?></td>
														<td><?php echo esc_html($cat['slug']); ?></td>
														<td><?php echo (int) $cat['sort_order']; ?></td>
														<td>
															<span class="badge badge-<?php echo $active ? 'success' : 'secondary'; ?>">
																<?php echo $active ? 'Yes' : 'No'; ?>
															</span>
														</td>
														<td>
															<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_attr(site_url('Website/categories?edit='.(int) $cat['id'])); ?>">Edit</a>
															<a class="btn btn-sm btn-outline-<?php echo $active ? 'warning' : 'success'; ?>"
															   href="<?php echo esc_attr(site_url('Website/category_toggle/'.(int) $cat['id'])); ?>"
															   onclick="return confirm('<?php echo $active ? 'Mark this category inactive?' : 'Activate this category?'; ?>');">
																<?php echo $active ? 'Inactive' : 'Activate'; ?>
															</a>
															<a class="btn btn-sm btn-outline-danger"
															   href="<?php echo esc_attr(site_url('Website/category_delete/'.(int) $cat['id'])); ?>"
															   onclick="return confirm('Delete this category permanently?');">
																Delete
															</a>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>

								<?php if ($active_tab === 'products') {
									$products = isset($products) ? $products : array();
									$categories = isset($categories) ? $categories : array();
									$edit_product = isset($edit_product) && is_array($edit_product) ? $edit_product : NULL;
									$edit_image_url = isset($edit_image_url) ? (string) $edit_image_url : '';
									$is_edit = ! empty($edit_product);
									$ep = $is_edit ? $edit_product : array();
								?>
									<?php if ( ! $categories) { ?>
										<div class="alert alert-info">
											No categories yet.
											<a href="<?php echo esc_attr(site_url('Website/categories')); ?>">Add a category</a>
											before creating products.
										</div>
									<?php } ?>
									<form method="post" action="<?php echo esc_attr(site_url('Website/product_save')); ?>" class="mb-4 border rounded p-3">
										<?php $this->load->view('partials/csrf_field'); ?>
										<input type="hidden" name="id" value="<?php echo $is_edit ? (int) $ep['id'] : 0; ?>">
										<div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
											<h5 class="mb-0"><?php echo $is_edit ? 'Edit product #'.(int) $ep['id'] : 'Add product'; ?></h5>
											<?php if ($is_edit) { ?>
												<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_attr(site_url('Website/products')); ?>">Cancel edit</a>
											<?php } ?>
										</div>
										<div class="form-row">
											<div class="form-group col-md-4">
												<label>Name (EN)</label>
												<input class="form-control" name="name_en" required value="<?php echo esc_attr(isset($ep['name_en']) ? $ep['name_en'] : ''); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>Category <a class="small" href="<?php echo esc_attr(site_url('Website/categories')); ?>">(+ add new)</a></label>
												<select class="form-control" name="category_id" required>
													<option value="">Select category…</option>
													<?php foreach ($categories as $cat) {
														$cid = (int) $cat['id'];
														$selected = ($is_edit && (int) (isset($ep['category_id']) ? $ep['category_id'] : 0) === $cid) ? ' selected' : '';
													?>
														<option value="<?php echo $cid; ?>"<?php echo $selected; ?>>
															<?php echo esc_html($cat['name_en'].(!empty($cat['name_ur']) ? ' / '.$cat['name_ur'] : '')); ?>
															<?php echo empty($cat['is_active']) ? ' (inactive)' : ''; ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class="form-group col-md-2">
												<label>SKU</label>
												<input class="form-control" name="sku" value="<?php echo esc_attr(isset($ep['sku']) ? $ep['sku'] : ''); ?>">
											</div>
											<div class="form-group col-md-2">
												<label>Price</label>
												<input class="form-control" name="price" type="number" step="0.01" min="0" required value="<?php echo esc_attr(isset($ep['price']) ? $ep['price'] : ''); ?>">
											</div>
											<div class="form-group col-md-4">
												<label>Image URL</label>
												<input class="form-control" name="image_url" value="<?php echo esc_attr($edit_image_url); ?>">
											</div>
											<div class="form-group col-md-8">
												<label>Short description</label>
												<input class="form-control" name="short_desc_en" value="<?php echo esc_attr(isset($ep['short_desc_en']) ? $ep['short_desc_en'] : ''); ?>">
											</div>
											<div class="form-group col-md-2">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="is_active" value="1" <?php echo (!$is_edit || !empty($ep['is_active'])) ? 'checked' : ''; ?>> Active</label>
											</div>
											<div class="form-group col-md-2">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="is_featured" value="1" <?php echo ($is_edit && !empty($ep['is_featured'])) ? 'checked' : ''; ?>> Featured</label>
											</div>
										</div>
										<button class="btn btn-primary" type="submit"><?php echo $is_edit ? 'Update product' : 'Save product'; ?></button>
									</form>
									<div class="table-responsive">
										<table class="table table-sm table-striped">
											<thead>
												<tr>
													<th>ID</th>
													<th>Name</th>
													<th>Category</th>
													<th>SKU</th>
													<th>Price</th>
													<th>Active</th>
													<th>Slug</th>
													<th style="min-width:180px">Actions</th>
												</tr>
											</thead>
											<tbody>
												<?php if ( ! $products) { ?>
													<tr><td colspan="8" class="text-muted">No products yet.</td></tr>
												<?php } ?>
												<?php foreach ($products as $p) {
													$active = ! empty($p['is_active']);
												?>
													<tr>
														<td><?php echo (int) $p['id']; ?></td>
														<td><?php echo esc_html($p['name_en']); ?></td>
														<td><?php echo esc_html(!empty($p['category_name']) ? $p['category_name'] : '—'); ?></td>
														<td><?php echo esc_html($p['sku']); ?></td>
														<td><?php echo esc_html(number_format((float) $p['price'], 2)); ?></td>
														<td>
															<span class="badge badge-<?php echo $active ? 'success' : 'secondary'; ?>">
																<?php echo $active ? 'Yes' : 'No'; ?>
															</span>
														</td>
														<td><?php echo esc_html($p['slug']); ?></td>
														<td>
															<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_attr(site_url('Website/products?edit='.(int) $p['id'])); ?>">Edit</a>
															<a class="btn btn-sm btn-outline-<?php echo $active ? 'warning' : 'success'; ?>"
															   href="<?php echo esc_attr(site_url('Website/product_toggle/'.(int) $p['id'])); ?>"
															   onclick="return confirm('<?php echo $active ? 'Mark this product inactive?' : 'Activate this product?'; ?>');">
																<?php echo $active ? 'Inactive' : 'Activate'; ?>
															</a>
															<a class="btn btn-sm btn-outline-danger"
															   href="<?php echo esc_attr(site_url('Website/product_delete/'.(int) $p['id'])); ?>"
															   onclick="return confirm('Delete this product permanently?');">
																Delete
															</a>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>

								<?php if ($active_tab === 'orders') {
									$orders = isset($orders) ? $orders : array();
								?>
									<div class="table-responsive">
										<table class="table table-sm table-striped">
											<thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Total</th><th>Status</th><th>Payment</th><th>Created</th><th>Update</th></tr></thead>
											<tbody>
												<?php if ( ! $orders) { ?>
													<tr><td colspan="8" class="text-muted">No website orders yet.</td></tr>
												<?php } ?>
												<?php foreach ($orders as $o) { ?>
													<tr>
														<td><?php echo esc_html($o['order_number']); ?></td>
														<td><?php echo esc_html($o['guest_name']); ?></td>
														<td><?php echo esc_html($o['guest_phone']); ?></td>
														<td><?php echo esc_html(number_format((float) $o['total'], 2)); ?></td>
														<td><?php echo esc_html($o['status']); ?></td>
														<td><?php echo esc_html($o['payment_method'].'/'.$o['payment_status']); ?></td>
														<td><?php echo esc_html($o['created_at']); ?></td>
														<td>
															<form method="post" action="<?php echo esc_attr(site_url('Website/order_status/'.$o['id'])); ?>" class="form-inline">
																<?php $this->load->view('partials/csrf_field'); ?>
																<select name="status" class="form-control form-control-sm mr-1">
																	<?php foreach (array('pending','confirmed','processing','shipped','delivered','cancelled') as $st) { ?>
																		<option value="<?php echo $st; ?>" <?php echo $o['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
																	<?php } ?>
																</select>
																<button class="btn btn-sm btn-secondary" type="submit">Save</button>
															</form>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>

								<?php if ($active_tab === 'guides') {
									$guides = isset($guides) ? $guides : array();
									$catalog = isset($catalog) ? $catalog : array();
								?>
									<form method="post" action="<?php echo esc_attr(site_url('Website/guide_save')); ?>" class="mb-4 border rounded p-3">
										<?php $this->load->view('partials/csrf_field'); ?>
										<h5>Add / update measurement tutorial</h5>
										<div class="form-row">
											<div class="form-group col-md-3">
												<label>Field key</label>
												<select class="form-control" name="field_key" required>
													<option value="">Select</option>
													<?php foreach ($catalog as $group) { ?>
														<optgroup label="<?php echo esc_attr($group['label_en']); ?>">
															<?php foreach ($group['fields'] as $f) { ?>
																<option value="<?php echo esc_attr($f['key']); ?>"><?php echo esc_html($f['key'].' — '.$f['label_en']); ?></option>
															<?php } ?>
														</optgroup>
													<?php } ?>
												</select>
											</div>
											<div class="form-group col-md-2">
												<label>Garment</label>
												<input class="form-control" name="garment" placeholder="kurta">
											</div>
											<div class="form-group col-md-3">
												<label>Label EN</label>
												<input class="form-control" name="label_en">
											</div>
											<div class="form-group col-md-4">
												<label>Video URL</label>
												<input class="form-control" name="video_url" placeholder="https://...">
											</div>
											<div class="form-group col-md-6">
												<label>Instruction EN</label>
												<textarea class="form-control" name="instruction_en" rows="2"></textarea>
											</div>
											<div class="form-group col-md-6">
												<label>Instruction UR</label>
												<textarea class="form-control" name="instruction_ur" rows="2" dir="rtl"></textarea>
											</div>
											<div class="form-group col-md-3">
												<label>Example</label>
												<input class="form-control" name="example_value">
											</div>
											<div class="form-group col-md-3">
												<label class="d-block">&nbsp;</label>
												<label><input type="checkbox" name="is_active" value="1" checked> Active</label>
											</div>
										</div>
										<button class="btn btn-primary" type="submit">Save tutorial</button>
									</form>
									<div class="table-responsive">
										<table class="table table-sm table-striped">
											<thead><tr><th>Field</th><th>Garment</th><th>Label</th><th>Video</th><th>Active</th></tr></thead>
											<tbody>
												<?php foreach ($guides as $g) { ?>
													<tr>
														<td><?php echo esc_html($g['field_key']); ?></td>
														<td><?php echo esc_html($g['garment']); ?></td>
														<td><?php echo esc_html($g['label_en']); ?></td>
														<td><?php echo $g['video_url'] ? 'Yes' : '—'; ?></td>
														<td><?php echo !empty($g['is_active']) ? 'Yes' : 'No'; ?></td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>

								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
