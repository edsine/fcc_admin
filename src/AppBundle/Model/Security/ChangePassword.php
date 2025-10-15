<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 8/20/2017
 * Time: 7:41 AM
 */

namespace AppBundle\Model\Security;


class ChangePassword
{

    private $oldPassword;
    private $newPassword;
    private $confirmNewPassword;
    private $userProfileGuid;

    /**
     * ChangePassword constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param mixed $oldPassword
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @return mixed
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param mixed $newPassword
     */
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
    }

    /**
     * @return mixed
     */
    public function getConfirmNewPassword()
    {
        return $this->confirmNewPassword;
    }

    /**
     * @param mixed $confirmNewPassword
     */
    public function setConfirmNewPassword($confirmNewPassword)
    {
        $this->confirmNewPassword = $confirmNewPassword;
    }

    /**
     * @return mixed
     */
    public function getUserProfileGuid()
    {
        return $this->userProfileGuid;
    }

    /**
     * @param mixed $userProfileGuid
     */
    public function setUserProfileGuid($userProfileGuid)
    {
        $this->userProfileGuid = $userProfileGuid;
    }


}