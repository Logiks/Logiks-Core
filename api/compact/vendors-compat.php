<?php
/*
 * Backwards-compatible class aliases for Composer-installed dependencies.
 *
 * Older Logiks code was written against the PHPMailer 5 global class names that used to be
 * bundled in api/vendors/. PHPMailer 6 is namespaced, so the old names are aliased to the
 * new classes. Nothing is aliased when the dependency is not installed.
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(class_exists('PHPMailer\\PHPMailer\\PHPMailer') && !class_exists('PHPMailer', false)) {
	class_alias('PHPMailer\\PHPMailer\\PHPMailer', 'PHPMailer');
}
if(class_exists('PHPMailer\\PHPMailer\\SMTP') && !class_exists('SMTP', false)) {
	class_alias('PHPMailer\\PHPMailer\\SMTP', 'SMTP');
}
if(class_exists('PHPMailer\\PHPMailer\\Exception') && !class_exists('phpmailerException', false)) {
	class_alias('PHPMailer\\PHPMailer\\Exception', 'phpmailerException');
}
