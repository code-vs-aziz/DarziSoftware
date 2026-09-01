# Application translations (i18n)

Central config and modular language files. **Do not hardcode user-facing text in views.**

## Structure

```
application/config/app_languages.php   ← allowed languages, RTL, file list
application/helpers/app_lang_helper.php ← t(), app_set_language(), etc.
application/hooks/App_language_hook.php ← loads lang files on every request
application/language/
  english/   *_lang.php
  urdu/      *_lang.php
  arabic/    *_lang.php
```

## Usage in PHP views

```php
<?php echo t('menu_customer'); ?>
<?php echo t('app_record_entered', array($id)); ?>
```

## Usage in JavaScript

Strings exposed via `window.APP_LANG` in `head.php` (keys from `js_lang.php`).

## Add a new string

1. Add the same key to **all** language folders, e.g. `english/app_lang.php`:
   ```php
   $lang['my_new_key'] = 'Hello';
   ```
2. Add Urdu and Arabic translations in `urdu/app_lang.php` and `arabic/app_lang.php`.
3. Use `t('my_new_key')` in the view.

## Add a new language

1. Copy `application/language/english/` to `application/language/{code}/`.
2. Translate every `$lang[...]` value.
3. Register in `application/config/app_languages.php`:
   - `app_language_codes`
   - `app_languages` (label, html_lang, dir, css_class, rtl)

## Switch language

- UI: header / sidebar / login switcher → `Language/set/{code}`
- Persists in session + cookie (`app_language`)

## Modules (lang files)

| File | Domain |
|------|--------|
| app_lang.php | Common UI, dashboard |
| menu_lang.php | Sidebar / breadcrumbs |
| auth_lang.php | Login, access denied |
| customer_lang.php | Customer screens |
| order_lang.php | Orders, lifecycle |
| transfer_lang.php | Pipeline / transfer |
| cash_lang.php | Cash receipts |
| report_lang.php | Reports |
| master_lang.php | Employee, style, suit |
| config_lang.php | Business configuration |
| rbac_lang.php | User management |
| print_lang.php | Print views |
| js_lang.php | JavaScript alerts |

## Rules

- Never change `$lang` **keys** across languages — only values.
- Print views and legacy forms may still contain English; migrate gradually to `t()`.
- Language switch uses full page reload (`data-no-ajax="true"`) so all strings refresh.
