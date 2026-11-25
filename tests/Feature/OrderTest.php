<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_can_submit_multiple_products_and_receive_a_transaction_number(): void
    {
        $coffee = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);
        $latte = Product::create([
            'nama_produk' => 'Latte',
            'harga' => 22000,
            'stok' => 4,
        ]);

        $response = $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'delivery',
            'alamat' => 'Jalan Merdeka No. 1',
            'items' => [
                $coffee->id => 2,
                $latte->id => 1,
            ],
            'catatan' => [
                $coffee->id => 'Panas dan tambah gula',
            ],
            'metode_pembayaran' => 'qris',
        ]);

        $response->assertRedirect(route('pesan.index', ['jenis' => 'delivery']));
        $response->assertSessionHas('order_number', 1);
        $response->assertSessionHas('order_total', 58000);
        $response->assertSessionHas('order_payment_method', 'qris');
        $response->assertSessionHas('order_type', 'delivery');
        $this->assertDatabaseHas('pesanan', [
            'id' => 1,
            'jumlah_produk' => 3,
            'total' => 58000,
            'tipe_pesanan' => 'delivery',
            'alamat' => 'Jalan Merdeka No. 1',
            'metode_pembayaran' => 'qris',
        ]);
        $this->assertDatabaseHas('detail_pesanan', [
            'pesanan_id' => 1,
            'produk_id' => $coffee->id,
            'qty' => 2,
            'subtotal' => 36000,
            'catatan' => 'Panas dan tambah gula',
        ]);
        $this->assertDatabaseHas('produk', ['id' => $coffee->id, 'stok' => 3]);
        $this->assertDatabaseHas('produk', ['id' => $latte->id, 'stok' => 3]);
    }

    public function test_customer_order_page_is_separate_from_the_company_profile(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
            'gambar' => 'images/products/kopi-nias.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('pesan.index'))
            ->assertDontSee('Pesan Delivery')
            ->assertDontSee('Take In / Beli di Tempat')
            ->assertDontSee('Lihat Menu')
            ->assertDontSee('name="metode_pembayaran"');

        $this->get(route('pesan.index'))
            ->assertOk()
            ->assertSee('Metode pembayaran')
            ->assertSee('name="tipe_pesanan" value="delivery"', false)
            ->assertDontSee('type="radio" name="tipe_pesanan"', false)
            ->assertSee('Alamat pengantaran')
            ->assertSee('required class=', false)
            ->assertSee('QRIS')
            ->assertSee('Tunai')
            ->assertSee(asset('images/products/kopi-nias.jpg'))
            ->assertSee('name="catatan['.$product->id.']"', false)
            ->assertSee('tambah gula');

        $this->get(route('pesan.index', ['jenis' => 'takein']))
            ->assertOk()
            ->assertSee('name="tipe_pesanan" value="takein"', false)
            ->assertDontSee('type="radio" name="tipe_pesanan"', false)
            ->assertDontSee('Alamat pengantaran');
    }

    public function test_product_detail_and_cart_allow_customers_to_collect_multiple_products(): void
    {
        $coffee = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
            'deskripsi' => 'Kopi pilihan dari Nias.',
        ]);
        $latte = Product::create([
            'nama_produk' => 'Latte',
            'harga' => 22000,
            'stok' => 3,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('produk.show', $coffee));
        $this->get(route('produk.show', $coffee))
            ->assertOk()
            ->assertSee('Kopi pilihan dari Nias.')
            ->assertSee('Tambah ke keranjang')
            ->assertSee('Pesan sekarang');

        $this->post(route('keranjang.add', $coffee), ['quantity' => 2])
            ->assertRedirect(route('keranjang.index'));
        $this->post(route('keranjang.add', $latte), [
            'quantity' => 1,
            'next' => 'checkout',
        ])->assertRedirect(route('keranjang.index').'#checkout');

        $this->get(route('keranjang.index'))
            ->assertOk()
            ->assertSee('Kopi Nias')
            ->assertSee('Latte')
            ->assertSee('Checkout semua produk')
            ->assertSee('Rp58.000');
        $this->assertSame([
            $coffee->id => 2,
            $latte->id => 1,
        ], session('cart'));

        $this->patch(route('keranjang.update', $coffee), ['quantity' => 3])
            ->assertRedirect(route('keranjang.index'));
        $this->delete(route('keranjang.remove', $latte))
            ->assertRedirect(route('keranjang.index'));
        $this->get(route('keranjang.index'))
            ->assertOk()
            ->assertSee('Rp54.000')
            ->assertDontSee('Latte');
        $this->assertSame([$coffee->id => 3], session('cart'));
    }

    public function test_cart_rejects_quantities_above_stock_and_checkout_clears_the_cart(): void
    {
        $coffee = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 2,
        ]);

        $this->post(route('keranjang.add', $coffee), ['quantity' => 2])
            ->assertRedirect(route('keranjang.index'));
        $this->from(route('keranjang.index'))
            ->post(route('keranjang.add', $coffee), ['quantity' => 1])
            ->assertRedirect(route('keranjang.index'))
            ->assertSessionHasErrors('quantity');
        $this->assertSame([$coffee->id => 2], session('cart'));

        $this->post(route('keranjang.checkout'), [
            'nama' => 'Pelanggan',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'metode_pembayaran' => 'cash',
        ])->assertRedirect(route('keranjang.index'))
            ->assertSessionHas('order_number', 1)
            ->assertSessionMissing('cart');

        $this->assertDatabaseHas('pesanan', [
            'id' => 1,
            'jumlah_produk' => 2,
            'total' => 36000,
            'tipe_pesanan' => 'takein',
        ]);
        $this->assertDatabaseHas('produk', ['id' => $coffee->id, 'stok' => 0]);
    }

    public function test_qris_cart_checkout_and_cashier_detail_display_the_supplied_qris_image(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi QRIS',
            'harga' => 22000,
            'stok' => 3,
        ]);

        $this->assertFileExists(public_path('images/qris.png'));
        $this->post(route('keranjang.add', $product), ['quantity' => 1])
            ->assertRedirect(route('keranjang.index'));
        $this->post(route('keranjang.checkout'), [
            'nama' => 'Pelanggan QRIS',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'metode_pembayaran' => 'qris',
        ])->assertRedirect(route('keranjang.index'));

        $this->get(route('keranjang.index'))
            ->assertOk()
            ->assertSee('Metode pembayaran: QRIS')
            ->assertSee(asset('images/qris.png'));

        $cashier = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($cashier)
            ->get(route('kasir.pesanan.show', 1))
            ->assertOk()
            ->assertSee(asset('images/qris.png'))
            ->assertDontSee('QRIS belum dikonfigurasi');
    }

    public function test_order_is_rejected_when_payment_method_is_invalid(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);

        $this->from(route('pesan.index'))
            ->post(route('pesan.store'), [
                'nama' => 'Pelanggan',
                'nomor_hp' => '08123456789',
                'tipe_pesanan' => 'delivery',
                'alamat' => 'Jalan Merdeka No. 1',
                'items' => [$product->id => 1],
                'metode_pembayaran' => 'transfer',
            ])
            ->assertRedirect(route('pesan.index'))
            ->assertSessionHasErrors('metode_pembayaran');

        $this->assertDatabaseCount('pesanan', 0);
        $this->assertDatabaseHas('produk', ['id' => $product->id, 'stok' => 5]);
    }

    public function test_cashier_can_still_view_orders_and_the_selected_payment_method(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);
        $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'items' => [$product->id => 1],
            'catatan' => [$product->id => 'Dingin'],
            'metode_pembayaran' => 'cash',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pesanan.index'))
            ->assertOk()
            ->assertSee('Pesanan & Transaksi')
            ->assertSee('Tunai')
            ->assertSee('Take In');

        $this->actingAs($admin)
            ->get(route('admin.pesanan.show', 1))
            ->assertOk()
            ->assertSee('Keterangan:')
            ->assertSee('Kopi Nias')
            ->assertSee('Dingin');
    }

    public function test_cashier_daily_order_value_only_includes_completed_orders(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);

        foreach ([2, 1] as $quantity) {
            $this->post(route('pesan.store'), [
                'nama' => 'Pelanggan',
                'nomor_hp' => '08123456789',
                'tipe_pesanan' => 'takein',
                'items' => [$product->id => $quantity],
                'metode_pembayaran' => 'cash',
            ])->assertRedirect();
        }

        $cashier = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($cashier)
            ->patch(route('kasir.pesanan.status', 2), ['status' => 'selesai'])
            ->assertRedirect();

        $this->actingAs($cashier)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-slate-950">Rp18.000</p>', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pesanan.index'))
            ->assertOk()
            ->assertSee('Pesanan Hari Ini')
            ->assertSee('Menunggu Diproses')
            ->assertSee('Pesanan Selesai')
            ->assertSee('Nilai Pesanan Hari Ini')
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-slate-950">2</p>', false)
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-amber-600">1</p>', false)
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-emerald-600">1</p>', false)
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-slate-950">Rp18.000</p>', false);
    }

    public function test_admin_daily_sales_only_include_completed_orders(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);

        foreach ([2, 1] as $quantity) {
            $this->post(route('pesan.store'), [
                'nama' => 'Pelanggan',
                'nomor_hp' => '08123456789',
                'tipe_pesanan' => 'takein',
                'items' => [$product->id => $quantity],
                'metode_pembayaran' => 'cash',
            ])->assertRedirect();
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.pesanan.status', 2), ['status' => 'selesai'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Penjualan Hari Ini')
            ->assertSee('<p class="mt-4 text-3xl font-semibold text-slate-950">Rp18.000</p>', false);
    }

    public function test_seeded_cashier_can_log_in_and_use_the_cashier_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        $this->post(route('login.post'), [
            'email' => 'kasir@example.com',
            'password' => 'kasir123',
        ])->assertRedirect(route('kasir.dashboard'));

        $this->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Kasir')
            ->assertSee('Pesanan Hari Ini')
            ->assertSee('Pesanan Terbaru');

        $this->get(route('kasir.pesanan.index'))->assertOk();
        $this->get(route('admin.dashboard'))->assertRedirect('/login');
    }

    public function test_cashier_qris_order_displays_qris_payment_panel(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi QRIS',
            'harga' => 22000,
            'stok' => 5,
        ]);

        $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan QRIS',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'items' => [$product->id => 1],
            'metode_pembayaran' => 'qris',
        ]);

        $order = DB::table('pesanan')->where('nama', 'Pelanggan QRIS')->first();
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($cashier)
            ->get(route('kasir.pesanan.show', $order->id))
            ->assertOk()
            ->assertSee('Pembayaran QRIS')
            ->assertSee('Rp22.000');
    }

    public function test_removed_admin_sections_return_not_found(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/user')->assertNotFound();
        $this->actingAs($admin)->get('/admin/diskon')->assertNotFound();
    }

    public function test_cashier_and_admin_can_set_only_the_three_order_statuses(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);
        $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'items' => [$product->id => 1],
            'metode_pembayaran' => 'cash',
        ]);

        $orderId = 1;
        $cashier = User::factory()->create(['role' => 'kasir']);

        foreach (['pending', 'proses', 'selesai'] as $status) {
            $this->actingAs($cashier)
                ->patch(route('kasir.pesanan.status', $orderId), ['status' => $status])
                ->assertRedirect();
            $this->assertDatabaseHas('pesanan', ['id' => $orderId, 'status' => $status]);
        }

        $this->actingAs($cashier)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('Pesanan Selesai')
            ->assertSee('<p class="mt-3 text-3xl font-semibold text-emerald-600">1</p>', false);

        $this->actingAs($cashier)
            ->get(route('kasir.pesanan.index'))
            ->assertOk()
            ->assertSee('<option value="pending"', false)
            ->assertSee('<option value="proses"', false)
            ->assertSee('<option value="selesai"', false);

        $this->from(route('kasir.pesanan.index'))
            ->patch(route('kasir.pesanan.status', $orderId), ['status' => 'dibatalkan'])
            ->assertRedirect(route('kasir.pesanan.index'))
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('pesanan', ['id' => $orderId, 'status' => 'selesai']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.pesanan.status', $orderId), ['status' => 'proses'])
            ->assertRedirect();
        $this->assertDatabaseHas('pesanan', ['id' => $orderId, 'status' => 'proses']);
        $this->get(route('admin.pesanan.index'))
            ->assertOk()
            ->assertSee('<option value="pending"', false)
            ->assertSee('<option value="proses"', false)
            ->assertSee('<option value="selesai"', false);
    }

    public function test_delivery_requires_an_address_but_take_in_does_not(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);
        $baseOrder = [
            'nama' => 'Pelanggan',
            'nomor_hp' => '08123456789',
            'items' => [$product->id => 1],
            'metode_pembayaran' => 'cash',
        ];

        $this->from(route('pesan.index'))
            ->post(route('pesan.store'), $baseOrder + ['tipe_pesanan' => 'delivery'])
            ->assertRedirect(route('pesan.index'))
            ->assertSessionHasErrors('alamat');

        $this->post(route('pesan.store'), $baseOrder + ['tipe_pesanan' => 'takein'])
            ->assertRedirect(route('pesan.index', ['jenis' => 'takein']));

        $this->assertDatabaseHas('pesanan', ['id' => 1, 'tipe_pesanan' => 'takein', 'alamat' => null]);
    }

    public function test_admin_delivery_and_take_in_pages_only_show_their_order_type(): void
    {
        $product = Product::create([
            'nama_produk' => 'Kopi Nias',
            'harga' => 18000,
            'stok' => 5,
        ]);
        $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan Delivery',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'delivery',
            'alamat' => 'Jalan Merdeka No. 1',
            'items' => [$product->id => 1],
            'metode_pembayaran' => 'cash',
        ]);
        $this->post(route('pesan.store'), [
            'nama' => 'Pelanggan Take In',
            'nomor_hp' => '08123456789',
            'tipe_pesanan' => 'takein',
            'items' => [$product->id => 1],
            'metode_pembayaran' => 'qris',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pesanan Online (Delivery)')
            ->assertSee('Pesanan Beli di Tempat (Take In)');
        $this->actingAs($admin)
            ->get(route('admin.delivery.index'))
            ->assertOk()
            ->assertSee('Pelanggan Delivery')
            ->assertDontSee('Pelanggan Take In');
        $this->actingAs($admin)
            ->get(route('admin.takein.index'))
            ->assertOk()
            ->assertSee('Pelanggan Take In')
            ->assertDontSee('Pelanggan Delivery');
    }

    public function test_admin_delivery_and_take_in_pages_hide_order_value_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.takein.index'))
            ->assertOk()
            ->assertSee('Pesanan Take In')
            ->assertDontSee('Nilai Pesanan Take In');

        $this->actingAs($admin)
            ->get(route('admin.delivery.index'))
            ->assertOk()
            ->assertSee('Pesanan Delivery')
            ->assertDontSee('Nilai Pesanan Delivery');
    }
}
