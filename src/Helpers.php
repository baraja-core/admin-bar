<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


final class Helpers
{
	public static function escapeHtml(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}


	public static function isHtmlMode(): bool
	{
		return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === ''
			&& ($_SERVER['HTTP_X_TRACY_AJAX'] ?? '') === ''
			&& PHP_SAPI !== 'cli'
			&& preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list())) !== 1;
	}


	public static function minifyHtml(string $haystack): string
	{
		return (string) preg_replace_callback(
			'#[ \t\r\n]+|<(/)?(textarea|pre)(?=\W)#i',
			static fn(array $match) => !isset($match[2]) || $match[2] === ''
					? ' '
					: $match[0],
			$haystack,
		);
	}


	public static function iconHelp(): string
	{
		return '<svg viewBox="0 0 12.7 12.7"><defs/><g transform="translate(0,-284.29998)"><circle cx="6.3500376" cy="290.64993" id="path4504" r="3.8805983" style="opacity:1;vector-effect:none;fill:none;fill-opacity:0.58506224;stroke:#fff;stroke-width:0.70555556;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1"/><path d="m 5.2916669,289.94443 c 0,-0.70557 0.3527779,-1.05835 1.0583334,-1.05835 0.7055555,0 1.0583337,0.35278 1.0583334,1.05835 3e-7,0.70555 -1.0583334,0 -1.0583334,1.41109" id="path4506" style="fill:none;fill-rule:evenodd;stroke:#fff;stroke-width:0.705px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"/><rect height="0.70555556" style="opacity:1;vector-effect:none;fill:#fff;fill-opacity:1;stroke:none;stroke-width:0.70555556;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" width="0.70555556" x="5.9972234" y="292.06107"/></g></svg>';
	}


	public static function iconUser(): string
	{
		return '<svg viewBox="0 0 12.699999 12.7"><g transform="translate(0 -284.3)"><path d="m1.41127 295.30682 3.033889-1.83442s-1.27-2.04614-1.27-4.1628c0-2.11667 1.0583333-3.24555 3.175-3.24555 2.1166664 0 3.1749998 1.12888 3.1749998 3.24555 0 2.11666-1.27 4.1628-1.27 4.1628l3.0338892 1.83442" fill="none" stroke="#fff" stroke-width=".706967"/><path d="m1.5522222 295.58885 3.2455555-2.11667s.8466666.70555 1.5522224.70555c.7055555 0 1.5522221-.70555 1.5522221-.70555l3.2455558 2.11667z" fill-opacity=".376471"/></g></svg>';
	}
}
