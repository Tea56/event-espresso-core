<div class="padding">
	<p>
        <?php _e('Displays a list of events based on a set of criteria on a WordPress page or post. Unless otherwise specified, events are sorted by start date.', 'event_espresso'); ?> <?php echo sprintf( __('For a full list of available shortcodes, please view the %sshortcode documentation%s on our website.', 'event_espresso'), '<a href="https://eventespresso.com/wiki/setup-event-espresso-arabica-theme/">', '</a>' ); ?>
    </p>
		<ul>
			<li><strong><?php _e('Show a list of all of your events', 'event_espresso'); ?></strong><br /> [ESPRESSO_EVENTS]</li>
			<li><strong><?php _e('Set a custom title for the event list', 'event_espresso'); ?></strong><br /> [ESPRESSO_EVENTS title="My Super Event List"]</li>
			<li><strong><?php _e('Limit (paginate) the number of events that are shown in the event list on a page or post', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS limit=5]</li>
			<li><strong><?php _e('Add a custom CSS class to each event post/article', 'event_espresso'); ?></strong><br /> [ESPRESSO_EVENTS css_class=my-custom-class]</li>
			<li><strong><?php _e('Filter the event list by month and year', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS month="<?php echo date( 'F Y' ); ?>"]</li>
			<li><strong><?php _e('Show expired events in the event list', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS show_expired=true]</li>
			<li><strong><?php _e('Sorts the event list in ascending order', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS sort=ASC]</li>
			<li><strong><?php _e('Sorts the event list in descending order', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS sort=DESC]</li>
			<li><strong><?php _e('Order the event list by a specific set of parameters (refer to available options below)', 'event_espresso'); ?></strong><br />[ESPRESSO_EVENTS order_by=start_date,id]</li>
		</ul>
            <p>
                <?php _e('These parameters (options) are available for the shortcode above. Multiple parameters should be separated by a comma.', 'event_espresso'); ?>
            </p>
            id<br />
            start_date<br />
            end_date<br />
            event_name<br />
            category_slug<br />
            ticket_start<br />
            ticket_end<br />
            venue_title<br />
            city<br />
            state
		</p>
</div>