<?php

namespace Tests\Feature;

use App\Models\Canteen;
use App\Models\CanteenSale;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanteenSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_canteen_officer_can_view_sales_page_and_navbar_has_pencatatan_link(): void
    {
        $canteen = Canteen::create(['name' => 'Kantin Utama']);
        $classroom = Classroom::create(['name' => 'PPLG X-1', 'grade_level' => 10]);

        $user = User::create([
            'name' => 'Petugas Kantin',
            'email' => 'canteen@wikrama.test',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'nis' => '12610019',
            'classroom_id' => $classroom->id,
        ]);

        DutyAssignment::create([
            'user_id' => $user->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => 'canteen',
            'floor_id' => null,
            'canteen_id' => $canteen->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('duty.sales.index'));

        $response->assertStatus(200);
        $response->assertSee('Pencatatan Penjualan');
        $response->assertSee('Input Data Makanan yang Dijual');
        $response->assertSee('Nama Makanan yang Dijual');
        $response->assertSee('Harga Satuan (Rp)');
        $response->assertSee('Stok Awal');
        $response->assertSee('Terjual Berapa');
        $response->assertSee('Total Hasil Penjualan Keseluruhan');
    }

    public function test_canteen_officer_can_store_and_calculate_sales(): void
    {
        $canteen = Canteen::create(['name' => 'Kantin Utama']);
        $classroom = Classroom::create(['name' => 'PPLG X-1', 'grade_level' => 10]);
        $user = User::create([
            'name' => 'Petugas Kantin',
            'email' => 'canteen@wikrama.test',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'nis' => '12610019',
            'classroom_id' => $classroom->id,
        ]);

        DutyAssignment::create([
            'user_id' => $user->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => 'canteen',
            'floor_id' => null,
            'canteen_id' => $canteen->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('duty.sales.store'), [
            'item_name' => 'Roti Bakar Coklat',
            'price' => 5000,
            'initial_stock' => 20,
            'quantity' => 15,
        ]);

        $response->assertRedirect(route('duty.sales.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('canteen_sales', [
            'user_id' => $user->id,
            'item_name' => 'Roti Bakar Coklat',
            'price' => 5000,
            'initial_stock' => 20,
            'quantity' => 15,
        ]);

        // Check calculation: 15 * 5000 = 75000
        $sale = CanteenSale::where('item_name', 'Roti Bakar Coklat')->latest()->first();
        $this->assertEquals(75000, $sale->total);
        $this->assertEquals(5, $sale->remaining_stock);
    }
}
