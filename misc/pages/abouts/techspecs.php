<?php
echo "<table width=100% border=0>
				  <tr>
					  <td width=200px>Framework Info</td><td width=10px align=center>:</td><td valign=top>".Framework_Title." v".Framework_Version." ".Framework_Status."</td>					  
				  </tr>
				  <tr>
					<td>App Sites Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.APPS_FOLDER,"*","apps.cfg")." WebApps</td>
				  </tr>
				  <tr><td colspan=10><hr/></td></tr>
				  <tr>
					<td>Core Module Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.PLUGINS_FOLDER."modules/","dir_only")." Modules</td>
				  </tr>				  
				  <tr>
					<td>Core Widget Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.PLUGINS_FOLDER."widgets/","*")." Widgets</td>
				  </tr>
				  <tr>
					<td>Installed Helpers Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.HELPERS_FOLDER,"file_only")." Helpers</td>
				  </tr>
				  <tr>
					<td>Installed Libraries Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.API_FOLDER."libs/","*")." Libraries</td>
				  </tr>
				  <tr>
					<td>Installed JS Libraries Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.JS_FOLDER,"dir_only")." Libraries</td>
				  </tr>
				  <tr><td colspan=10><hr/></td></tr>
				  <tr>
					<td>Installed Themes Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.THEME_FOLDER,"dir_only")." Themes</td>
				  </tr>
				  <tr>
					<td>Installed JQuery Skins Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.SKINS_FOLDER."jquery/","file_only")." Themes</td>
				  </tr>
				  <tr>
					<td>Background Images Count</td><td width=10px align=center>:</td><td valign=top>".countInDir(ROOT.MEDIA_FOLDER."cssbg/","file_only")." Themes</td>
				  </tr>
				  <tr><td colspan=10><hr/></td></tr>
			  </table>";
?>
