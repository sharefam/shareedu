<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\AnnouncementDataTag;
use Topxia\Service\Common\ServiceKernel;
use Biz\User\CurrentUser;

class AnnouncementDataTagTest extends BaseTestCase
{
    public function testGetDataWithParentUser()
    {
        $user = $this->createNewUser();
        $user['orgId'] = '1';
        $user['orgCode'] = '1.';
        $user['org'] = array('id' => 1, 'name' => 'fullSite', 'parentId' => '0', 'orgCode' => '1.');
        $this->setNewUserOrg($user);
        $dataTag = new AnnouncementDataTag();
        $announcement = $dataTag->getData(array('count' => '1'));
        $this->assertCount(0, $announcement);
    }

    public function testGetDataWithUser()
    {
        $user = $this->createNewUser();
        $user['orgId'] = '2';
        $user['orgCode'] = '1.2.';
        $user['org'] = array('id' => 2, 'name' => 'fullSite', 'parentId' => '1', 'orgCode' => '1.2.');
        $this->setNewUserOrg($user);
        $this->createNewAnnouncement();
        $dataTag = new AnnouncementDataTag();
        $announcement = $dataTag->getData(array('count' => '1'));
        $this->assertCount(1, $announcement);
    }

    public function testGetDataWithChildUser()
    {
        $user = $this->createNewUser();
        $user['orgId'] = '3';
        $user['orgCode'] = '1.2.3.';
        $user['org'] = array('id' => 3, 'name' => 'fullSite', 'parentId' => '2', 'orgCode' => '1.2.3.');
        $this->setNewUserOrg($user);
        $this->createNewAnnouncement();
        $dataTag = new AnnouncementDataTag();
        $announcement = $dataTag->getData(array('count' => '1'));
        $this->assertCount(1, $announcement);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataWithUpperLimitCount()
    {
        $count = '101';
        $dataTag = new AnnouncementDataTag();
        $dataTag->getData(array($count));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataWithEmptyCount()
    {
        $count = '';
        $dataTag = new AnnouncementDataTag();
        $dataTag->getData(array($count));
    }

    private function createNewUser()
    {
        $user = array();
        $user['email'] = 'user@test.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user12345';

        return $this->getUserService()->register($user);
    }

    private function setNewUserOrg($user)
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
    }

    private function createNewAnnouncement()
    {
        $this->getOrgService()->createOrg(array('name' => 'TagA', 'code' => 'CodeA', 'parentId' => '0'));
        $this->getOrgService()->createOrg(array('name' => 'TagB', 'code' => 'CodeB', 'parentId' => '1'));
        $time = time();
        $testAnnouncementField = array(
            'content' => 'This is a FullSite Announcement',
            'startTime' => '1448902861',
            'endTime' => $time,
            'targetType' => 'global',
            'orgCode' => '1.2.',
            'url' => 'http://',
        );
        $testOrgField = array(
            'id' => '2',
            'orgCode' => '1.2.',
        );
        $default = array(
            'enable_org' => 1,
        );
        $this->getSettingService()->set('magic', $default);
        $this->mockBiz('Org:OrgService');
        $this->getOrgService()->shouldReceive('getOrgByOrgCode')->andReturn($testOrgField);
        $announcement = $this->getAnnouncementService()->createAnnouncement($testAnnouncementField);
        $this->assertNotEmpty($announcement);

        return $announcement;
    }

    /**
     * @return AnnouncementService
     */
    private function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement:AnnouncementService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
