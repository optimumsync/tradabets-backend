<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;

use App\Helpers\InboxNotificationHelper;

//use App\Traits\BaseModel;

class InboxNotification extends Model
{
    //use SoftDeletes, SoftCascadeTrait, BaseModel;

    // set
    protected $table = 'inbox_notification';
    protected $fillable = ['subject', 'body', 'read_at','receiver'];

    protected $primaryKey = 'inbox_notification_id';



    /**
     * Get the index notifiable title.
     */
    public function getApplicableToAttribute()
    {
        // set
        $morph_applicable_to_arr = config('custom-project.morph-applicable-to-arr');
        $applicable_to = '';
        $applicable_to_class = '';

        // check
        if($this->inbox_notifiable){
            // set
            $inbox_notifiable_class = get_class($this->inbox_notifiable);

            // check
            if($inbox_notifiable_class == 'App\Models\SharedUserAction'){
                $applicable_to_class = get_class($this->inbox_notifiable->shared_user_actionable);
            }
        }

        // set
        $applicable_to = data_get($morph_applicable_to_arr, $applicable_to_class, '');

        return $applicable_to;
    }

    /**
     * Get the action type if applicable
     */
    public function getActionTypeDescriptionAttribute()
    {
        $action_type_description = '';

        // check
        if($this->inbox_notifiable){
            // check
            if($this->inbox_notifiable_type == 'App\Models\SharedUserAction'){
                $action_type_description = $this->inbox_notifiable->action_type_description;
            }
        }

        return $action_type_description;
    }


    /**
     * Get all of the owning inbox_notifiable models.
     */
    public function inbox_notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Sets the read status.
     */
    public function was_read($inbox_notification)
    {
        // check
        if(!$inbox_notification->read_at){
            // update
            $inbox_notification->update([
                'read_at' => now()
            ]);

            // update
			InboxNotificationHelper::update_inbox_notification_sessions();
        }

        return $inbox_notification;
    }

}
