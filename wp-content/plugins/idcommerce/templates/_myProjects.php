<li class="myprojects column-3 author-<?php echo $post->post_author; ?>" data-author="<?php echo $post->post_author; ?>">
	<div class="myproject_wrapper">
      <div class="project-item">
          <div class="project-thumb image" style="<?php echo (!empty($thumb) ? 'background-image: url('.$thumb.');' : ''); ?>"></div>
          <div title="Project Status" class="project-status <?php echo strtolower($status); ?>">
             <?php echo (strtoupper($status) == 'PUBLISH' ? __('PUBLISHED', 'memberdeck') : $status); ?>
          </div>
          <div class="project-item-wrapper <?php echo strtolower($status); ?>">
              <div class="option-list">
              <?php 
              $actions = '<a title="Edit Project" href="'.md_get_durl().$prefix.'edit_project='.$post_id.'"><i class="fa fa-edit"></i></a>';
              $actions .= '<a title="Upload File" href="'.md_get_durl().$prefix.'project_files='.$post_id.'"><i class="fa fa-cloud-upload"></i></a>';
              $actions .= '<a title="View Project" href="'.$permalink.'"><i class="fa fa-eye"></i></a>';
              $actions .= '<a title="Export Orders" href="'.md_get_durl().$prefix.'export_project='.$post_id.'"><i class="fa fa-share-square-o"></i></a>';
              echo apply_filters('id_myprojects_actions', $actions, $post, $user_id);
              ?>
              </div>
         </div>
         <div title="Project Name" class="project-name"><?php echo get_the_title($post_id); ?></div>
         <div class="project-funded"><?php echo $project_raised; ?> <?php _e('Raised', 'ignitiondeck'); ?></div>
      </div>
    </div>
</li>