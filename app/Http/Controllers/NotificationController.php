<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\CreateRequest;
use App\Http\Requests\Notification\UpdateRequest;
use App\Http\Permissions\NotificationPermission;
use App\Http\Requests\Notification\SendNotificationToSalonRequest;
use App\Http\Services\NotificationService;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = $this->notificationService->index(request()->all());

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => ResponseService::meta($notifications),
        ]);
    }

    public function show($id)
    {
        $notification = $this->notificationService->show($id);

        NotificationPermission::canShow($notification);

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification),
        ]);
    }

    // public function create(CreateRequest $request)
    // {
    //     $data = NotificationPermission::create($request->validated());

    //     $notification = $this->notificationService->create($data);

    //     return response()->json([
    //         'success' => true,
    //         'message' => trans('messages.notification.item_created_successfully'),
    //         'data' => new NotificationResource($notification),
    //     ]);
    // }

    // public function update($id, UpdateRequest $request)
    // {
    //     $notification = $this->notificationService->show($id);

    //     // NotificationPermission::canUpdate($notification, $request->validated());

    //     $notification = $this->notificationService->update($notification, $request->validated());

    //     return response()->json([
    //         'success' => true,
    //         'message' => trans('messages.notification.item_updated_successfully'),
    //         'data' => new NotificationResource($notification),
    //     ]);
    // }

    public function destroy($id)
    {
        $notification = $this->notificationService->show($id);

        NotificationPermission::canDelete($notification);

        $deleted = $this->notificationService->destroy($notification);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? trans('messages.notification.item_deleted_successfully')
                : trans('messages.notification.failed_delete_item'),
        ]);
    }

    // public function sendNotificationToSalonOwner($id, SendNotificationToSalonRequest $request)
    // {
    //     $last_notification = $this->notificationService->sendNotificationToSalonOnwer($id, $request->validated());

    //     return response()->json([
    //         'success' => true,
    //         'message' => trans('messages.notification.send_notification_successfully'),
    //         'data' => new NotificationResource($last_notification),
    //     ]);
    // }

    public function sendNotificationToAllUsers(SendNotificationToSalonRequest $request)
    {
        $last_notification = $this->notificationService->sendNotificationToAllUsers($request->validated());

        return response()->json([
            'success' => true,
            'message' => trans('messages.notification.send_notification_successfully'),
            'data' => new NotificationResource($last_notification),
        ]);
    }


    public function unreadCount()
    {

        $count = User::notificationsUnreadCount();


        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'test' => Auth::check(),
            ],
        ]);
    }

    public function readNotification($id)
    {


        $notification = $this->notificationService->show($id);

        // NotificationPermission::canShow($notification);

        $notifications = $this->notificationService->readNotification($notification->id);

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
        ]);
    }
}
