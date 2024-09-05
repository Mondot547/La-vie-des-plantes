<div class="app-container">
	<div class="main-container">
		<div id="gs-woowps-slider-shortcode-app">
			<header class="gs-woo-slider-header">
				<div class="gs-containeer-f">
					<div class="gs-roow">
						<div class="woo-area gs-col-xs-6">
							<router-link to="/"><img src="<?php echo GSWPS_FILES_URI . '/assets/img/woo.svg'; ?>" alt="Woo Product Views"></router-link>
						</div>
						<div class="menu-area gs-col-xs-6 text-right">
							<ul>
								<router-link to="/" tag="li"><a><?php _e('Shortcodes', 'gswps'); ?></a></router-link>
								<router-link to="/shortcode" tag="li"><a><?php _e('Create New', 'gswps'); ?></a></router-link>
								<router-link to="/preferences" tag="li"><a><?php _e('Preferences', 'gswps'); ?></a></router-link>
								<router-link to="/config-layouts" tag="li"><a><?php _e('Config Layouts', 'gswps'); ?></a></router-link>
								<router-link to="/demo-data" tag="li"><a><?php _e('Demo Data', 'gswps'); ?></a></router-link>
							</ul>
						</div>
					</div>
				</div>
			</header>

			<div class="gs-woo-slider-app-view-container">
				<router-view :key="$route.fullPath"></router-view>
			</div>

		</div>
	</div>
</div>