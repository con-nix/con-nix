<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test notification creation with all fields.
     */
    public function test_notification_creation_with_all_fields(): void
    {
        $user = User::factory()->create();
        $data = ['key' => 'value', 'number' => 123];
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'test_notification',
            'title' => 'Test Title',
            'message' => 'Test message content',
            'data' => $data,
            'action_url' => 'https://example.com/action',
        ]);
        
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('test_notification', $notification->type);
        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test message content', $notification->message);
        $this->assertEquals($data, $notification->data);
        $this->assertEquals('https://example.com/action', $notification->action_url);
        $this->assertNull($notification->read_at);
        $this->assertFalse($notification->isRead());
    }

    /**
     * Test notification creation with minimal fields.
     */
    public function test_notification_creation_with_minimal_fields(): void
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'simple_notification',
            'title' => 'Simple Title',
        ]);
        
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('simple_notification', $notification->type);
        $this->assertEquals('Simple Title', $notification->title);
        $this->assertNull($notification->message);
        $this->assertNull($notification->data);
        $this->assertNull($notification->action_url);
        $this->assertNull($notification->read_at);
    }

    /**
     * Test marking notification as read.
     */
    public function test_marking_notification_as_read(): void
    {
        $notification = Notification::factory()->create(['read_at' => null]);
        
        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->read_at);
        
        $notification->markAsRead();
        
        $this->assertTrue($notification->fresh()->isRead());
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertInstanceOf(Carbon::class, $notification->fresh()->read_at);
    }

    /**
     * Test marking notification as unread.
     */
    public function test_marking_notification_as_unread(): void
    {
        $notification = Notification::factory()->create(['read_at' => now()]);
        
        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->read_at);
        
        $notification->markAsUnread();
        
        $this->assertFalse($notification->fresh()->isRead());
        $this->assertNull($notification->fresh()->read_at);
    }

    /**
     * Test marking already read notification as read again.
     */
    public function test_marking_already_read_notification_as_read(): void
    {
        $originalReadTime = Carbon::now()->subHour();
        $notification = Notification::factory()->create(['read_at' => $originalReadTime]);
        
        $this->assertTrue($notification->isRead());
        
        $notification->markAsRead();
        
        // Should not change the read_at timestamp if already read
        $this->assertEquals($originalReadTime->toDateTimeString(), $notification->fresh()->read_at->toDateTimeString());
    }

    /**
     * Test unread notifications scope.
     */
    public function test_unread_notifications_scope(): void
    {
        $user = User::factory()->create();
        
        $unreadNotification1 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);
        
        $unreadNotification2 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);
        
        $readNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);
        
        $unreadNotifications = Notification::unread()->get();
        
        $this->assertEquals(2, $unreadNotifications->count());
        $this->assertTrue($unreadNotifications->contains('id', $unreadNotification1->id));
        $this->assertTrue($unreadNotifications->contains('id', $unreadNotification2->id));
        $this->assertFalse($unreadNotifications->contains('id', $readNotification->id));
    }

    /**
     * Test read notifications scope.
     */
    public function test_read_notifications_scope(): void
    {
        $user = User::factory()->create();
        
        $readNotification1 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);
        
        $readNotification2 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now()->subHour(),
        ]);
        
        $unreadNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);
        
        $readNotifications = Notification::read()->get();
        
        $this->assertEquals(2, $readNotifications->count());
        $this->assertTrue($readNotifications->contains('id', $readNotification1->id));
        $this->assertTrue($readNotifications->contains('id', $readNotification2->id));
        $this->assertFalse($readNotifications->contains('id', $unreadNotification->id));
    }

    /**
     * Test recent notifications scope.
     */
    public function test_recent_notifications_scope(): void
    {
        $user = User::factory()->create();
        
        $oldNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);
        
        $newerNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(2),
        ]);
        
        $newestNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);
        
        $recentNotifications = Notification::recent()->get();
        
        $this->assertEquals($newestNotification->id, $recentNotifications->first()->id);
        $this->assertEquals($newerNotification->id, $recentNotifications->get(1)->id);
        $this->assertEquals($oldNotification->id, $recentNotifications->last()->id);
    }

    /**
     * Test notification belongs to user relationship.
     */
    public function test_notification_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
        $this->assertEquals($user->name, $notification->user->name);
    }

    /**
     * Test data array casting.
     */
    public function test_data_array_casting(): void
    {
        $data = [
            'string' => 'value',
            'number' => 42,
            'boolean' => true,
            'array' => ['nested', 'values'],
            'null' => null,
        ];
        
        $notification = Notification::factory()->create(['data' => $data]);
        
        $freshNotification = Notification::find($notification->id);
        
        $this->assertIsArray($freshNotification->data);
        $this->assertEquals($data, $freshNotification->data);
        $this->assertEquals('value', $freshNotification->data['string']);
        $this->assertEquals(42, $freshNotification->data['number']);
        $this->assertTrue($freshNotification->data['boolean']);
        $this->assertIsArray($freshNotification->data['array']);
        $this->assertNull($freshNotification->data['null']);
    }

    /**
     * Test read_at datetime casting.
     */
    public function test_read_at_datetime_casting(): void
    {
        $readTime = Carbon::now()->subHours(2);
        $notification = Notification::factory()->create(['read_at' => $readTime]);
        
        $freshNotification = Notification::find($notification->id);
        
        $this->assertInstanceOf(Carbon::class, $freshNotification->read_at);
        $this->assertEquals($readTime->toDateTimeString(), $freshNotification->read_at->toDateTimeString());
    }

    /**
     * Test notification fillable fields.
     */
    public function test_notification_fillable_fields(): void
    {
        $user = User::factory()->create();
        $data = ['test' => 'data'];
        
        $notification = new Notification();
        $notification->fill([
            'user_id' => $user->id,
            'type' => 'test_type',
            'title' => 'Test Title',
            'message' => 'Test Message',
            'data' => $data,
            'action_url' => 'https://test.com',
            'read_at' => now(),
            'id' => 999, // This should not be fillable
            'created_at' => Carbon::now()->subYear(), // This should not be fillable
        ]);
        
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('test_type', $notification->type);
        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test Message', $notification->message);
        $this->assertEquals($data, $notification->data);
        $this->assertEquals('https://test.com', $notification->action_url);
        $this->assertNotNull($notification->read_at);
        
        // These should not be set via fill
        $this->assertNull($notification->id);
        $this->assertNull($notification->created_at);
    }

    /**
     * Test notification factory states.
     */
    public function test_notification_factory_creates_valid_notification(): void
    {
        $notification = Notification::factory()->create();
        
        $this->assertNotNull($notification->user_id);
        $this->assertNotNull($notification->type);
        $this->assertNotNull($notification->title);
        $this->assertNotNull($notification->message);
        $this->assertIsArray($notification->data);
        $this->assertInstanceOf(User::class, $notification->user);
    }

    /**
     * Test creating notification with null data.
     */
    public function test_notification_with_null_data(): void
    {
        $notification = Notification::factory()->create(['data' => null]);
        
        $this->assertNull($notification->data);
        
        $freshNotification = Notification::find($notification->id);
        $this->assertNull($freshNotification->data);
    }

    /**
     * Test creating notification with empty data array.
     */
    public function test_notification_with_empty_data_array(): void
    {
        $notification = Notification::factory()->create(['data' => []]);
        
        $this->assertIsArray($notification->data);
        $this->assertEmpty($notification->data);
        
        $freshNotification = Notification::find($notification->id);
        $this->assertIsArray($freshNotification->data);
        $this->assertEmpty($freshNotification->data);
    }
}
