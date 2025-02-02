<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Covers the profile action
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait ProfileTrait
{
    /**
     * Profile action
     *
     * @param mixed $id Profile id object.
     * @return mixed
     */
    public function profile($id = null)
    {
        $identity = $this->getRequest()->getAttribute('identity');
        $identity = $identity ?? [];
        $loggedUserId = $identity['id'] ?? null;
        $isCurrentUser = false;
        if (!Configure::read('Users.Profile.viewOthers') || empty($id)) {
            $id = $loggedUserId;
        }
        try {
            $appContain = (array)Configure::read('Users.Profile.contain', []);
            $user = $this->getUsersTable()->get($id, [
                    'contain' => array_merge($appContain, [
                        'SocialAccounts',
                    ]),
                ]);
            $this->set('avatarPlaceholder', Configure::read('Users.Avatar.placeholder'));
            if ($user->id === $loggedUserId) {
                $isCurrentUser = true;
            }
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'User was not found'));

            return $this->redirect($this->getRequest()->referer());
        } catch (InvalidPrimaryKeyException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'Not authorized, please login first'));

            return $this->redirect($this->getRequest()->referer());
        }
        $this->set(['user' => $user, 'isCurrentUser' => $isCurrentUser]);
        $this->set('_serialize', ['user', 'isCurrentUser']);
    }
}
