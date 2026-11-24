<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use Database\Seeders\VoterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VotingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_voter_seeder_includes_birth_dates(): void
    {
        $this->seed(VoterSeeder::class);

        $this->assertSame(10, User::query()->where('role', 'siswa')->count());
        $this->assertDatabaseHas('users', [
            'nisn' => '2026000001',
            'birth_date' => '2008-02-14',
        ]);
    }

    public function test_admin_login_uses_password_and_students_login_with_nisn_and_birth_date(): void
    {
        $siswa = User::factory()->create([
            'nisn' => '0012345678',
            'birth_date' => '2007-04-15',
            'role' => 'siswa',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@sekolah.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        $siswa->update([
            'email' => 'siswa@sekolah.com',
            'password' => 'siswa123',
        ]);
        $this->post('/login', ['email' => $siswa->email, 'password' => 'siswa123'])
            ->assertSessionHasErrors('email');

        $this->get('/siswa/login')
            ->assertOk()
            ->assertSee('name="nisn"', false)
            ->assertSee('name="birth_date"', false)
            ->assertDontSee('Token sekolah')
            ->assertDontSee('name="password"', false);

        $this->post('/siswa/login', [
            'nisn' => $siswa->nisn,
            'birth_date' => '2007-04-15',
        ])->assertRedirect('/vote');
        $this->assertAuthenticatedAs($siswa);

        $this->post('/logout');
        $this->from('/siswa/login')->post('/siswa/login', [
            'nisn' => $siswa->nisn,
            'birth_date' => '2007-04-16',
        ])->assertRedirect('/siswa/login')->assertSessionHasErrors('nisn');

        $this->post('/login', ['email' => $admin->email, 'password' => 'admin123'])
            ->assertRedirect('/admin');
    }

    public function test_public_student_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_home_page_has_three_clear_destinations(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Lanjut ke halaman admin')
            ->assertSee('Lanjut untuk memilih')
            ->assertSee('Lanjut mengenal paslon')
            ->assertSee('href="'.route('login').'"', false);

        $response->assertSee('href="'.route('voter.login').'"', false);
        $response->assertSee('href="'.route('candidates').'"', false);
    }

    public function test_candidate_introduction_page_is_public_and_shows_candidate_details(): void
    {
        Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Contoh',
            'nama_wakil' => 'Wakil Contoh',
            'visi' => 'Visi yang jelas',
            'misi' => 'Misi yang bermanfaat',
            'visi_misi' => 'Visi yang jelas',
            'foto' => 'https://example.com/paslon.jpg',
        ]);

        $this->get('/paslon')
            ->assertOk()
            ->assertSee('candidate-intro-hero', false)
            ->assertSee('candidate-intro-summary', false)
            ->assertSee('class="candidate-intro-count">1</strong>', false)
            ->assertSee('Baca visi dan misi setiap paslon sebelum menentukan pilihan.')
            ->assertSee('Lanjut pilih')
            ->assertSee('Ketua Contoh')
            ->assertSee('Wakil Contoh')
            ->assertSee('Visi yang jelas')
            ->assertSee('Misi yang bermanfaat')
            ->assertSee('https://example.com/paslon.jpg');
    }

    public function test_voting_pages_require_a_student_account(): void
    {
        $this->get('/vote')->assertRedirect('/siswa/login');
        $this->get('/admin')->assertRedirect('/login');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/vote')->assertForbidden();
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Hasil tiap pasangan')
            ->assertSee('Ubah informasi pasangan')
            ->assertSee('Ringkasan partisipasi')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertSee('+ Tambah Paslon')
            ->assertSee('action="'.route('admin.candidates.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertDontSee('Reset semua suara');
    }

    public function test_voting_page_uses_a_custom_confirmation_modal(): void
    {
        $voter = User::factory()->create(['role' => 'siswa']);
        Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Contoh',
            'nama_wakil' => 'Wakil Contoh',
            'visi_misi' => 'Visi paslon',
        ]);

        $this->actingAs($voter)
            ->get('/vote')
            ->assertOk()
            ->assertSee('Konfirmasi Pilihan')
            ->assertSee('Apakah Anda yakin memilih pasangan calon ini? Pilihan tidak dapat diubah setelah suara dikirim.')
            ->assertSee('Batal')
            ->assertSee('Ya, Kirim Suara')
            ->assertSee('data-vote-form', false)
            ->assertSee('name="_token"', false)
            ->assertSee('data-candidate-number="01"', false)
            ->assertSee('data-candidate-name="Ketua Contoh &amp; Wakil Contoh"', false)
            ->assertDontSee('onsubmit="return confirm', false);
    }

    public function test_admin_results_page_shows_vote_counts_and_is_restricted_to_admins(): void
    {
        $candidateOne = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Satu',
            'nama_wakil' => 'Wakil Satu',
            'visi_misi' => 'Visi misi satu',
        ]);
        $candidateTwo = Candidate::create([
            'nomor_urut' => 2,
            'nama_ketua' => 'Ketua Dua',
            'nama_wakil' => 'Wakil Dua',
            'visi_misi' => 'Visi misi dua',
        ]);
        $studentOne = User::factory()->create(['role' => 'siswa']);
        $studentTwo = User::factory()->create(['role' => 'siswa']);
        Vote::create(['user_id' => $studentOne->id, 'candidate_id' => $candidateOne->id]);
        Vote::create(['user_id' => $studentTwo->id, 'candidate_id' => $candidateOne->id]);

        $this->get('/admin/hasil')->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->get('/admin/hasil')
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/hasil')
            ->assertOk()
            ->assertSee('Ketua Satu')
            ->assertSee('Ketua Dua')
            ->assertSee('suara · 100.0%')
            ->assertSee('suara · 0.0%')
            ->assertSee('>2<', false)
            ->assertSee('>0<', false)
            ->assertSee('total suara tercatat');
    }

    public function test_admin_dashboard_shows_aggregate_participation_and_voter_list(): void
    {
        $candidate = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Pilihan',
            'nama_wakil' => 'Wakil Pilihan',
            'visi_misi' => 'Visi paslon',
        ]);
        $voter = User::factory()->create([
            'name' => 'Siswa Memilih',
            'kelas' => 'Kelas 10 - PPLG',
            'role' => 'siswa',
            'has_voted' => true,
        ]);
        User::factory()->create([
            'name' => 'Siswa Belum Memilih',
            'kelas' => 'Kelas 11 - BC',
            'role' => 'siswa',
            'has_voted' => false,
        ]);
        Vote::create(['user_id' => $voter->id, 'candidate_id' => $candidate->id]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Total siswa terdaftar')
            ->assertSee('Sudah berpartisipasi')
            ->assertSee('Belum berpartisipasi')
            ->assertSee('Daftar pemilih')
            ->assertSee('>2<', false)
            ->assertSee('>1<', false)
            ->assertSee('Siswa Memilih')
            ->assertSee('Siswa Belum Memilih')
            ->assertSee('Kelas 10 - PPLG')
            ->assertSee('Kelas 11 - BC')
            ->assertSee('Catatan impor')
            ->assertSee('voter-list-scroll', false)
            ->assertSee('role="region" aria-label="Daftar siswa terdaftar"', false)
            ->assertSee('status-voted', false)
            ->assertSee('status-pending', false)
            ->assertSeeInOrder([
                'Kelola paslon',
                'Ringkasan partisipasi &amp; hasil suara',
                'Hasil tiap pasangan',
                'Kelola Data Paslon',
                'Data Pemilih',
                'Import data pemilih',
                'Data siswa terdaftar',
            ], false);
    }

    public function test_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => 'admin@sekolah.com',
                'password' => 'wrong-password',
            ])->assertRedirect();
        }

        $this->post('/login', [
            'email' => 'admin@sekolah.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_student_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/siswa/login', [
                'nisn' => '0000000000',
                'birth_date' => '2007-04-15',
            ])->assertRedirect();
        }

        $this->post('/siswa/login', [
            'nisn' => '0000000000',
            'birth_date' => '2007-04-15',
        ])->assertTooManyRequests();
    }

    public function test_a_student_cannot_vote_twice(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $candidate = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Contoh',
            'nama_wakil' => 'Wakil Contoh',
            'visi' => 'Visi contoh',
            'misi' => 'Misi contoh',
            'visi_misi' => 'Visi contoh',
        ]);

        $this->actingAs($siswa)
            ->post('/vote', ['candidate_id' => $candidate->id])
            ->assertRedirect(route('vote.success'));
        $this->assertGuest();
        $this->get(route('vote.success'))->assertOk()->assertSee('Suara berhasil disimpan');

        $this->post('/vote', ['candidate_id' => $candidate->id])
            ->assertRedirect(route('voter.login'));

        $this->assertSame(1, Vote::where('user_id', $siswa->id)->count());
        $this->assertTrue($siswa->fresh()->has_voted);
    }

    public function test_voter_login_rejects_invalid_birth_date_and_already_voted_students(): void
    {
        $siswa = User::factory()->create([
            'nisn' => '0000000123',
            'birth_date' => '2008-01-30',
            'role' => 'siswa',
        ]);

        $this->from('/siswa/login')->post('/siswa/login', [
            'nisn' => '9999999999',
            'birth_date' => '2008-01-30',
        ])->assertRedirect('/siswa/login')->assertSessionHasErrors('nisn');

        $this->from('/siswa/login')->post('/siswa/login', [
            'nisn' => $siswa->nisn,
            'birth_date' => '2008-01-31',
        ])->assertRedirect('/siswa/login')->assertSessionHasErrors('nisn');

        $siswa->update(['has_voted' => true]);
        $this->from('/siswa/login')->post('/siswa/login', [
            'nisn' => $siswa->nisn,
            'birth_date' => '2008-01-30',
        ])->assertRedirect('/siswa/login')
            ->assertSessionHasErrors(['nisn' => 'Hak pilih Anda sudah digunakan dan tidak dapat memilih kembali.']);

        $this->assertGuest();
    }

    public function test_voter_import_adds_students_and_preserves_existing_vote_status(): void
    {
        $existingVoter = User::factory()->create([
            'nisn' => '0000000789',
            'name' => 'Nama Lama',
            'kelas' => 'Kelas 10 - PPLG',
            'role' => 'siswa',
            'has_voted' => true,
        ]);
        $csvPath = tempnam(sys_get_temp_dir(), 'voters-');
        file_put_contents($csvPath, "nisn,name,class,birth_date\n0000000789,Nama Baru,Kelas 11 - PPLG,2007-02-01\n0000000790,Siswa Baru,Kelas 10 - TO,2008-03-02\n");

        try {
            $this->artisan('pilketos:import-voters', ['file' => $csvPath])
                ->expectsOutputToContain('2 data siswa berhasil diimpor atau diperbarui.')
                ->assertSuccessful();
        } finally {
            unlink($csvPath);
        }

        $this->assertDatabaseHas('users', [
            'id' => $existingVoter->id,
            'name' => 'Nama Baru',
            'kelas' => 'Kelas 11 - PPLG',
            'birth_date' => '2007-02-01',
            'has_voted' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'nisn' => '0000000790',
            'name' => 'Siswa Baru',
            'kelas' => 'Kelas 10 - TO',
            'birth_date' => '2008-03-02',
            'role' => 'siswa',
            'has_voted' => false,
        ]);
    }

    public function test_admin_can_import_voters_with_birth_dates_from_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->createWithContent(
            'voters.csv',
            "nisn,name,class,birth_date\n0000000123,Siswa CSV,XI PPLG 1,2007-09-18\n",
        );

        $this->actingAs($admin)
            ->post(route('admin.voters.import'), ['csv_file' => $file])
            ->assertRedirect('/admin')
            ->assertSessionHas('success', 'Berhasil mengimpor 1 data pemilih!');

        $this->assertDatabaseHas('users', [
            'nisn' => '0000000123',
            'name' => 'Siswa CSV',
            'kelas' => 'XI PPLG 1',
            'birth_date' => '2007-09-18',
            'role' => 'siswa',
        ]);
    }

    public function test_admin_can_manage_candidates_and_reset_votes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        $candidate = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Lama',
            'nama_wakil' => 'Wakil Lama',
            'visi' => 'Visi lama',
            'misi' => 'Misi lama',
            'visi_misi' => 'Visi lama',
        ]);

        $this->actingAs($admin)
            ->put('/admin/paslon/'.$candidate->id, [
                'nomor_urut' => 3,
                'nama_ketua' => 'Ketua Baru',
                'nama_wakil' => 'Wakil Baru',
                'visi' => 'Visi baru',
                'misi' => 'Misi baru',
                'foto' => null,
            ])
            ->assertRedirect('/admin')
            ->assertSessionHas('success', 'Data pasangan calon berhasil diperbarui.');
        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'nomor_urut' => 3,
            'nama_ketua' => 'Ketua Baru',
            'nama_wakil' => 'Wakil Baru',
            'visi' => 'Visi baru',
            'misi' => 'Misi baru',
        ]);

        $this->post(route('admin.candidates.store'), [
            'nomor_urut' => 2,
            'nama_ketua' => 'Ketua Dua',
            'nama_wakil' => 'Wakil Dua',
            'visi' => 'Visi dua',
            'misi' => 'Misi dua',
            'foto' => null,
        ])->assertRedirect('/admin')
            ->assertSessionHas('success', 'Paslon baru berhasil ditambahkan!');
        $this->assertDatabaseHas('candidates', [
            'nomor_urut' => 2,
            'nama_ketua' => 'Ketua Dua',
            'nama_wakil' => 'Wakil Dua',
            'visi_misi' => 'Visi dua',
        ]);
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Paslon baru berhasil ditambahkan!')
            ->assertSee('Ketua Dua');

        $this->from('/admin')->post(route('admin.candidates.store'), [
            'nama_ketua' => '',
            'nama_wakil' => 'Wakil Kosong',
            'visi' => 'Visi',
            'misi' => 'Misi',
        ])->assertRedirect('/admin')->assertSessionHasErrors(['nomor_urut', 'nama_ketua']);
        $this->get('/admin')
            ->assertSee('Nomor urut wajib diisi.')
            ->assertSee('Nama ketua wajib diisi.')
            ->assertSee('field-error', false);
        $this->assertDatabaseCount('candidates', 2);

        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student)->get('/vote')
            ->assertOk()
            ->assertSee('Ketua Baru')
            ->assertSee('Ketua Dua')
            ->assertSeeInOrder(['Ketua Dua', 'Ketua Baru']);
        $this->get('/hasil')->assertOk()->assertSee('Ketua Baru')->assertSee('Ketua Dua');

        $this->actingAs($siswa)->post('/vote', ['candidate_id' => $candidate->id]);
        $this->actingAs($admin)
            ->delete('/admin/siswa/'.$siswa->id.'/reset-suara')
            ->assertRedirect('/admin');
        $this->assertFalse($siswa->fresh()->has_voted);
        $this->assertDatabaseMissing('votes', ['user_id' => $siswa->id]);

        $this->actingAs($siswa)->post('/vote', ['candidate_id' => $candidate->id]);
        $this->actingAs($admin)
            ->delete('/admin/paslon/'.$candidate->id)
            ->assertRedirect('/admin');
        $this->assertFalse($siswa->fresh()->has_voted);
        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
        $this->assertDatabaseMissing('votes', ['user_id' => $siswa->id]);
    }

    public function test_admin_can_create_candidate_with_a_leading_zero_ballot_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vision = 'Menjadikan OSIS sebagai wadah yang aktif, inovatif, dan berintegritas untuk mengembangkan potensi siswa serta menciptakan lingkungan sekolah yang harmonis dan berprestasi.';
        $mission = "- Mengadakan program kerja yang kreatif dan sesuai dengan minat bakat siswa.\n-";

        $this->actingAs($admin)
            ->post(route('admin.candidates.store'), [
                'nomor_urut' => '03',
                'nama_ketua' => 'Yusuf',
                'nama_wakil' => 'Septian',
                'foto_url' => 'https://example.com/yusuf-septian.jpg',
                'visi' => $vision,
                'misi' => $mission,
            ])
            ->assertRedirect('/admin')
            ->assertSessionHas('success', 'Paslon baru berhasil ditambahkan!');

        $this->assertDatabaseHas('candidates', [
            'nomor_urut' => 3,
            'nama_ketua' => 'Yusuf',
            'nama_wakil' => 'Septian',
            'foto' => 'https://example.com/yusuf-septian.jpg',
            'visi' => $vision,
            'misi' => $mission,
            'visi_misi' => $vision,
        ]);

        $this->get('/admin')->assertOk()->assertSee('Yusuf bersama Septian');
        $this->get('/hasil')->assertOk()->assertSee('Yusuf');
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->get('/vote')
            ->assertOk()
            ->assertSee('Yusuf')
            ->assertSee('Septian')
            ->assertSee($vision)
            ->assertSee($mission);
    }

    public function test_admin_can_upload_candidate_photo_and_view_it_on_public_candidate_page(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $candidate = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Foto',
            'nama_wakil' => 'Wakil Foto',
            'visi_misi' => 'Visi calon',
            'foto' => 'https://www.instagram.com/p/contoh/',
        ]);

        $this->actingAs($admin)
            ->get('/admin/paslon/'.$candidate->id.'/edit')
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="foto_file"', false)
            ->assertSee('accept="image/*"', false)
            ->assertSee('for="foto_file"', false)
            ->assertSee('data-photo-paste', false)
            ->assertDontSee('contenteditable="true"', false);

        $this->actingAs($admin)
            ->post('/admin/paslon/'.$candidate->id, [
                '_method' => 'PUT',
                'nomor_urut' => 1,
                'nama_ketua' => 'Ketua Foto',
                'nama_wakil' => 'Wakil Foto',
                'visi' => 'Visi calon',
                'misi' => 'Misi calon',
                'foto_url' => 'https://www.instagram.com/p/contoh/',
                'foto_file' => UploadedFile::fake()->createWithContent(
                    'paslon.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/aZ8AAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect('/admin')
            ->assertSessionHas('success');

        $candidate->refresh();
        $this->assertStringStartsWith('candidates/', $candidate->foto);
        Storage::disk('public')->assertExists($candidate->foto);
        $this->assertSame('/storage/'.$candidate->foto, $candidate->photoUrl());

        $this->get('/paslon')
            ->assertOk()
            ->assertSee($candidate->photoUrl(), false);
    }

    public function test_admin_can_set_candidate_photo_with_a_url_and_keep_it_when_editing_other_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $candidate = Candidate::create([
            'nomor_urut' => 1,
            'nama_ketua' => 'Ketua Link',
            'nama_wakil' => 'Wakil Link',
            'visi_misi' => 'Visi calon',
        ]);

        $this->actingAs($admin)
            ->put('/admin/paslon/'.$candidate->id, [
                'nomor_urut' => 1,
                'nama_ketua' => 'Ketua Link',
                'nama_wakil' => 'Wakil Link',
                'visi' => 'Visi calon',
                'misi' => 'Misi calon',
                'foto_url' => 'https://example.com/paslon.jpg',
            ])
            ->assertRedirect('/admin');

        $candidate->refresh();
        $this->assertSame('https://example.com/paslon.jpg', $candidate->foto);
        $this->assertSame('https://example.com/paslon.jpg', $candidate->photoUrl());
    }

}
