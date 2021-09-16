<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Plugin;


use Baraja\AdminBar\Helpers;

final class HelpPlugin implements Plugin
{
	public function render(): string
	{
		return '<a href="https://brj.cz/help" target="_blank">
			<table style="max-width:95px !important">
				<tr>
					<td style="width:40px;text-align:right">' . Helpers::iconHelp() . '</td>
					<td style="color:white !important;padding:0 4px !important">Help</td>
				</tr>
			</table>
		</a>';
	}
}
