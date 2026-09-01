<?php
$active = app_language();
$urls = app_lang_switcher_urls();
$wrap_class = isset($switcher_wrap_class) ? $switcher_wrap_class : '';
$languages = app_lang_config('app_languages');
if ( ! is_array($languages))
{
	$languages = array();
}
?>
<div class="app-lang-switcher-wrap <?php echo esc_html($wrap_class); ?>">
<div class="app-lang-switcher" role="group" aria-label="<?php echo esc_html(t('app_language')); ?>">
	<?php foreach (app_language_allowed() as $code) {
		$meta = isset($languages[$code]) ? $languages[$code] : array('label' => strtoupper($code));
		$label = isset($meta['label']) ? $meta['label'] : strtoupper($code);
		$is_active = ($active === $code) ? ' is-active' : '';
		$url = isset($urls[$code]) ? $urls[$code] : site_url('Language/set/'.$code);
	?>
	<a href="<?php echo esc_html($url); ?>" class="app-lang-switcher__btn<?php echo $is_active; ?>" lang="<?php echo esc_html(isset($meta['html_lang']) ? $meta['html_lang'] : $code); ?>" data-no-ajax="true"><?php echo esc_html($label); ?></a>
	<?php } ?>
</div>
</div>
