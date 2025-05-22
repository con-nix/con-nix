<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users can view their notifications.
     */
    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();
        $notifications = Notification::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertStatus(200)
            ->assertViewIs('notifications.index')
            ->assertViewHas('notifications');

        foreach ($notifications as $notification) {
            $response->assertSee($notification->title);
        }
    }

    /**
     * Test that users can only see their own notifications.
     */
    public function test_user_can_only_see_own_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $notification1 = Notification::factory()->create(['user_id' => $user1->id, 'title' => 'User 1 notification']);
        $notification2 = Notification::factory()->create(['user_id' => $user2->id, 'title' => 'User 2 notification']);

        $response = $this->actingAs($user1)
            ->get(route('notifications.index'));

        $response->assertStatus(200)
            ->assertSee($notification1->title)
            ->assertDontSee($notification2->title);
    }

    /**
     * Test that users can mark notifications as read.
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $this->assertTrue($notification->fresh()->read_at === null);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification));

        $response->assertStatus(302);
        $this->assertTrue($notification->fresh()->isRead());
    }

    /**
     * Test that users can mark notifications as unread.
     */
    public function test_user_can_mark_notification_as_unread(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);

        $this->assertTrue($notification->fresh()->isRead());

        $response = $this->actingAs($user)
            ->post(route('notifications.unread', $notification));

        $response->assertStatus(302);
        $this->assertFalse($notification->fresh()->isRead());
    }

    /**
     * Test that users can mark all notifications as read.
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.mark-all-read'));

        $response->assertStatus(302);

        foreach ($notifications as $notification) {
            $this->assertTrue($notification->fresh()->isRead());
        }
    }

    /**
     * Test that users can delete their notifications.
     */
    public function test_user_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete(route('notifications.destroy', $notification));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /**
     * Test that users cannot access other users' notifications.
     */
    public function test_user_cannot_access_other_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->post(route('notifications.read', $notification));

        $response->assertStatus(403);
    }

    /**
     * Test notification creation helper method.
     */
    public function test_notification_creation_helper(): void
    {
        $user = User::factory()->create();
        
        $notification = $user->createNotification(
            'test_type',
            'Test notification',
            'This is a test message',
            ['key' => 'value']
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('test_type', $notification->type);
        $this->assertEquals('Test notification', $notification->title);
        $this->assertEquals('This is a test message', $notification->message);
        $this->assertEquals(['key' => 'value'], $notification->data);
    }

    /**
     * Test unread notifications count.
     */
    public function test_unread_notifications_count(): void
    {
        $user = User::factory()->create();
        
        // Create 3 unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);
        
        // Create 2 read notifications
        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);

        $this->assertEquals(3, $user->unread_notifications_count);
    }
}
