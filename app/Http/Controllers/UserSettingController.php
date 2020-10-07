<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\UserLimit;
use App\UserProfile;
use App\UserSetting;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

class UserSettingController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param int $userId
     * @return JsonResponse
     */
    public function show($userId)
    {
        $user = app(User::class)
            ->with('profile', 'settings')
            ->withCount(['followers', 'followedUsers', 'likedLoops', 'uploadedLoops'])
            ->findOrFail($userId);

        return $this->success([
            'settings' => $user->settings
        ]);
    }

    public function update(User $user)
    {
        $settingsData = $this->request->get('settings');

        $settings = $user->settings()->updateOrCreate(['user_id' => $user->id], $settingsData);

        $user->setRelation('settings', $settings);

        return $this->success(['user' => $user]);
    }
}
