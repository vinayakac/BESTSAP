<?php
/**
 * Class for Project Loop
 */
class Fh_Project_Loop {
	private $project_id;
	private $post_id;
	private $post;
	private $posts;
	private $hDeck;
	// For the loop
	private $project_count;
	private $current_project;
	private $projects;
	private $project;
	private $summary;
	private $in_the_loop;
	private $wp_loop;

	function __construct() {
		add_filter('idcf_function_markup_echo', array($this, 'idcf_output_markup_fivehundred_functions'), 10, 2);
	}

	public function init() {
		$this->project_count = 0;
		unset($this->projects);
		unset($this->summary);
		unset($this->project);
		unset($this->posts);
		$this->in_the_loop = false;
		$this->current_project = -1;
		$this->wp_loop = false;
	}

	/********************************************************************************************************
	 *  Functions for using in hDeck or featured projects
	 ********************************************************************************************************/

	/**
	 * Function for getting all projects and adding into loop
	 */
	public function idcf_projects() {
		if (class_exists('ID_Project')) {
			$this->projects = ID_Project::get_all_projects();
			$this->project_count = count($this->projects);
		} else {
			$this->projects = array();
			$this->project_count = 0;
		}
	}

	public function idcf_wp_projects($wp_query_args) {
		$posts = get_posts($wp_query_args);
		$this->project_count = count($posts);
		$this->posts = $posts;
		$this->wp_loop = true;
	}

	/**
	 * Function to get hDeck depending on the arguments coming
	 */
	public function idcf_get_hdeck() {
		// see if hDeck is coming in arguments, if yes, don't need to get it
		if (empty($this->hDeck)) {
			$new_hdeck = new Deck($this->project_id);
			if (method_exists($new_hdeck, 'hDeck')) {
				$this->hDeck = $new_hdeck->hDeck();
			}
			else {
				// If this->post_id is not coming in arguments, get it using this->project_id
				if (empty($this->post_id)) {
					if (class_exists('ID_Project')) {
						$project = new ID_Project($this->project_id);
						$this->post_id = $project->get_project_postid();
					} else {
						$this->post_id = '';
					}
				}
				if (!empty($this->post_id)) {
					$this->hDeck = the_project_hDeck($this->post_id);
				}
			}
		}
		return $this->hDeck;
	}

	/**
	 * Filter to decide whether to echo or return markup
	 */
	public function idcf_output_markup_fivehundred_functions($markup, $is_echo) {
		if (!empty($is_echo) && $is_echo) {
			echo $markup;
			return '';
		}

		return $markup;
	}

	/**
	 * Function for getting the ID for the project
	 */
	public function the_ID($echo = true) {
		return apply_filters('idcf_function_markup_echo', (string) $this->project_id, $echo);
	}

	/**
	 * Function to display project title by default in <h3> tag
	 */
	public function idcf_project_title($echo = true) {
		$markup = '<h3>'.$this->summary->name.'</h3>';
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->summary->name), $echo);
	}

	/**
	 * Function to display project description by default inside <p> tag
	 */
	public function idcf_project_short_description($echo = true) {
		$markup = '<p>'.$this->summary->short_description.'</p>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}

	/**
	 * Function to get project goal html markup
	 */
	public function idcf_get_project_goal($featured = false, $echo = true, $no_markup = true) {
		if ($no_markup) {
			return apply_filters('idcf_function_markup_echo', $this->summary->goal, $echo);
		}
		if ($featured) {
			$markup = '<div class="featured-item">
							<strong>'.__('Goal',  'fivehundred').': </strong><span>'.$this->summary->goal.'</span>
						</div>';
		} else {
			$markup = '<div class="ign-product-goal" style="clear: both;">
							<div class="ign-goal">'.__('Goal', 'fivehundred').'</div> <strong>'.$this->summary->goal.' </strong>
						</div>';
		}
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->summary->goal), $echo);
	}

	/**
	 * Function to get project days left html markup
	 */
	public function idcf_project_days_left($featured = false, $echo = true, $only_days = false) {
		if ($featured) {
			$markup = '<div class="featured-item">
							<strong>'.__('Days Left',  'fivehundred').': </strong><span>'.$this->hDeck->days_left.'</span>
						</div>';
		} else {
			$markup = '<div class="ign-days-left">
							<strong>'.$this->hDeck->days_left.' '.__('Days Left', 'fivehundred').'</strong>
						</div>';
		}
		// If only days are required
		if ($only_days) {
			return apply_filters('idcf_function_markup_echo', $this->hDeck->days_left, $echo);
		}
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->hDeck->days_left), $echo);
	}

	/**
	 * Function to get project progress bar html
	 */
	public function idcf_project_progress_bar($echo = true) {
		$markup = '<div class="ign-progress-wrapper" style="clear: both;">
						<div class="ign-progress-percentage">
										'.$this->hDeck->percentage.'%
						</div> <!-- end progress-percentage -->
						<div style="width: '.$this->hDeck->percentage.'%" class="ign-progress-bar">
						
						</div><!-- end progress bar -->
					</div>';
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->hDeck->percentage), $echo);
	}

	/**
	 * Function to get the percentage funded
	 */
	public function idcf_project_funded_percent($echo = true, $round_figure = false) {
		$markup = $this->hDeck->percentage;
		// If need to remove extra zeroes if needed
		if ($round_figure) {
			// Exploding by decimal/period if exists
			$exploded_figure = explode(".", $markup);
			if (isset($exploded_figure[1])) {
				if ((int) $exploded_figure[1] > 0) {
					// After decimal points is not a zero value, so should dispay it
				} else {
					// 0 value after decimal point, so remove it
					$markup = $exploded_figure[0];
				}
			}
		}
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}

	/**
	 * Function to get project funds raised html
	 */
	public function idcf_project_raised_fund($featured = false, $echo = true, $no_markup = false) {
		if ($no_markup) {
			return apply_filters('idcf_function_markup_echo', $this->summary->total, $echo);
		}
		if ($featured) {
			$markup = '<div class="featured-item">
							<strong>'.__('Raised',  'fivehundred').': </strong><span>'.$this->summary->total.'</span>
						</div>';
		} else {
			$markup = '<div class="ign-progress-raised">
							<strong>'.$this->summary->total.'</strong>
							<div class="ign-raised">
								'.__('Raised', 'fivehundred').'
							</div>
						</div>';
		}
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->summary->total), $echo);
	}

	/**
	 * Function to get project supporters count html
	 */
	public function idcf_project_total_pledgers($featured = false, $echo = true, $no_markup = true) {
		if ($no_markup) {
			return apply_filters('idcf_function_markup_echo', $this->hDeck->pledges, $echo);
		}

		if ($featured) {
			$markup = '<div class="featured-item">
							<strong>'.__('Supporters',  'fivehundred').': </strong><span>'.$this->hDeck->pledgers.'</span>
						</div>';
		} else {
			$markup = '<div class="ign-product-supporters" style="clear: both;">
							<strong>'.$this->hDeck->pledges.'</strong>
							<div class="ign-supporters">
								'.__('Supporters', 'fivehundred').'
							</div>
						</div>';
		}
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->hDeck->pledges), $echo);
	}

	/**
	 * Function to get project Support Button html
	 */
	public function idcf_project_support_button($echo = true) {
		// Markup for support now button
		$markup = '<div class="ign-supportnow" data-projectid="'.$this->project_id.'">';
		if ($this->hDeck->end_type == 'closed' && $this->hDeck->days_left <= 0) {
			$markup .= '<a href="" class="">'.__('Project Closed', 'fivehundred').'</a>';
		} else {
			if (function_exists('is_id_licensed') && is_id_licensed()) {
				if (empty($permalinks) || $permalinks == '') {
					$markup .= 
						'<a href="'.the_permalink().'&purchaseform=500&amp;prodid='.(isset($this->project_id) ? $this->project_id : '').'">'.__('Support Now', 'fivehundred').'</a>';
				}
				else {
					$markup .= 
						'<a href="'.the_permalink().'?purchaseform=500&amp;prodid='.(isset($this->project_id) ? $this->project_id : '').'">'.__('Support Now', 'fivehundred').'</a>';
				}
			}
		}
		$markup .= '</div>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}

	/**
	 * Function to get Learn more button for featured project
	 */
	public function idcf_featured_project_learn_button($echo = true) {
		$markup = '<a class="featured-button" href="'.get_permalink($this->post_id).'">
						<span>'.__('Learn More', 'fivehundred').'</span>
					</a>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}

	/**
	 * Function to get project end date html markup
	 */
	public function idcf_project_end_date($echo = true, $no_markup = false) {
		if ($no_markup) {
			return apply_filters('idcf_function_markup_echo', $this->hDeck->month."/".$this->hDeck->day."/".$this->hDeck->year, $echo);
		}
		// Markup for end date
		$markup = '	<div class="ign-product-proposed-end"><span>'.__('Project Ends', 'fivehundred').':</span>
						<div id="ign-widget-date">
							<div id="ign-widget-month">'.__($this->hDeck->month, 'fivehundred').'</div>
							<div id="ign-widget-day">'.__($this->hDeck->day, 'fivehundred').'</div>
							<div id="ign-widget-year">'.__($this->hDeck->year, 'fivehundred').'</div>
						</div>
						<div class="clear"></div>
					</div>';
		return apply_filters('idcf_function_markup_echo', apply_filters('idcf_spit_markup', $markup, $this->hDeck->month."/".$this->hDeck->day."/".$this->hDeck->year), $echo);
	}

	/**
	 * Function to give markup when hDeck is not set
	 */
	public function idcf_no_hdeck_set($echo = false) {
		$markup = '<div id="ign-hDeck-wrapper">
						<div id="ign-hdeck-wrapperbg">
							<div id="ign-hDeck-header">
								<div id="ign-hDeck-left">
								</div>
								<div id="ign-hDeck-right">
									<div class="internal">
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}

	/**
	 * Function to give author name
	 */
	public function idcf_project_author_name($echo = true) {
		$author = get_user_by('id', $this->post->post_author);
		$author_name = $author->first_name . ' ' . $author->last_name;
		return apply_filters('idcf_function_markup_echo', $author_name, $echo);
	}

	/**
	 * Function to get project video field
	 */
	public function idcf_project_video($echo = true) {
		$video = the_project_video($this->post_id);
		return apply_filters('idcf_function_markup_echo', $video, $echo);
	}

	/**
	 * Function to get project image url
	 */
	public function idcf_project_image_url($echo = true) {
		return apply_filters('idcf_function_markup_echo', $this->summary->image_url, $echo);
	}

	/**
	 * Function to loop through projects
	 */
	public function idcf_have_projects() {
		// If current project + 1 is less than project count, then return true. This will keep the loop moving
		if (isset($this->project_count) && $this->project_count > 0 && ($this->current_project + 1) < $this->project_count) {
			return true;
		}
		else if (isset($this->project_count) && $this->project_count > 0 && ($this->current_project) == $this->project_count) {
			$this->current_project = -1;
			// Reset $this->project
			if ($this->project_count > 0) {
				if ($this->wp_loop) {
					$this->post = $this->posts[0];
					$project_id = get_post_meta($this->post->ID, 'ign_project_id', true);
					$id_project = new ID_Project($project_id);
					$this->project = $id_project->the_project();
				} else {
					$this->project = $this->projects[0];
				}
			}
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Function to get the project into hDeck
	 */
	public function idcf_the_project($wp_loop = false) {
		$this->in_the_loop = true;
		// $this->wp_loop = $wp_loop;
		$this->project = $this->idcf_next_project();
	}

	/**
	 * Function for getting next project in the loop
	 */
	public function idcf_next_project() {
		$this->current_project ++;

		if ($this->wp_loop) {
			// while (have_posts()) {
			// 	the_post();
			// }
			// global $post;
			// $this->post = $post;
			$this->post = $this->posts[$this->current_project];
			$project_id = get_post_meta($this->post->ID, 'ign_project_id', true);
			$this->project_id = $project_id;
			$this->post_id = $this->post->ID;
			$id_project = new ID_Project($project_id);
			$project = $id_project->the_project();
		}
		else {
			$project = $this->projects[$this->current_project];
			// Getting post_id of project using ID_Project class
			$id_project = new ID_Project($project->id);
			$this->post_id = $id_project->get_project_postid();
			$this->post = get_post($this->post_id);
			$this->project_id = $project->id;
		}

		$hdeck = new Deck($this->project_id);
		if (method_exists($hdeck, 'hDeck')) {
			$this->hDeck = $hdeck->hDeck();
		}
		else {
			$this->hDeck = the_project_hDeck($this->post_id);
		}
		$this->summary = the_project_summary($this->post_id);

		return $project;
	}

	/**
	 * Function for setting this->projects array to global project
	 */
	public function idcf_set_projects() {
		global $project, $post;
		$this->current_project = -1;
		// Adding the project to array if it's not empty
		if (!empty($project)) {
			$this->projects = array();
			array_push( $this->projects, $project );
			$this->post_id = $post->ID;
			$this->idcf_the_project();
		}
	}

	/**
	 * Function for getting the content of post of the ID project
	 */
	public function idcf_the_content() {
		$content = $this->post->post_content;
		return $content;
	}
}

$project_loop = new Fh_Project_Loop();
$project_loop->init();
$project = null;
?>