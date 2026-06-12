<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkController extends ApiController
{
    public function getWorks(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->data(ApplicationUser::query()
            ->where('id', '!=', $user->id)
            ->get()
            ->map(fn (ApplicationUser $workUser) => [
                'id' => $workUser->id,
                'name' => $workUser->name,
                'rating' => $this->userRating($workUser),
            ]));
    }

    public function sendRequest(Request $request, string $userId)
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        if (Application::where('user_id', $userId)->exists()) {
            return $this->fail('The application has already been sent', 409, 'Conflict');
        }

        $category = $user->categories()->first();

        if (! $category) {
            return $this->fail('Category not found', 404, 'Not Found');
        }

        Application::create([
            'id' => (string) Str::uuid(),
            'status' => 'PENDING',
            'category_id' => $category->id,
            'user_id' => $userId,
        ]);

        return $this->empty();
    }
}
