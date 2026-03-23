<x-admin-layout>
    <x-slot:title>User Management</x-slot:title>

    {{-- ── Toast ── --}}
    <div id="toast"
        class="fixed bottom-6 right-6 z-[9999] hidden items-center gap-3 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium"
        style="background:var(--bg-raised);border:1px solid var(--border);min-width:280px">
        <span id="toast-icon" class="text-lg flex-shrink-0"></span>
        <span id="toast-msg" style="color:var(--text-head)"></span>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-white)">User Management</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-dim)">
                Manage registered users, tiers, and access levels.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--green-dim);border:1px solid rgba(52,211,153,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--green)">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--green)">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-start gap-3 p-4 rounded-xl mb-6"
            style="background:var(--red-dim);border:1px solid rgba(248,113,113,0.25)">
            <svg class="flex-shrink-0 mt-0.5" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24" style="color:var(--red)">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <p class="text-sm font-medium" style="color:var(--red)">{{ session('error') }}</p>
        </div>
    @endif

    <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Table header --}}
        <div class="px-6 py-4" style="border-bottom:1px solid var(--border)">
            <h2 class="font-semibold" style="color:var(--text-white)">Registered Users</h2>
            <p class="font-mono text-[10px] mt-0.5" style="color:var(--text-dim)">
                {{ $users->total() }} total · ordered by newest
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full data-table">
                <thead>
                    <tr>
                        <th class="text-left">Name</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Organization</th>
                        <th class="text-center">Tier</th>
                        <th class="text-center">Access</th>
                        <th class="text-left">Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr id="user-row-{{ $user->id }}">
                            {{-- Name --}}
                            <td class="font-medium" style="color:var(--text-white)">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                                        style="background:var(--amber-dim);color:var(--amber)">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="text-[12px]" style="color:var(--text-body)">{{ $user->email }}</td>

                            {{-- Org --}}
                            <td class="text-[12px]" style="color:var(--text-body)">
                                {{ $user->organization ?? '—' }}
                            </td>

                            {{-- Tier — inline select --}}
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span id="tier-badge-{{ $user->id }}"
                                        class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="{{ (int) $user->tier >= 3 ? 'background:var(--amber-dim);color:var(--amber)' : ((int) $user->tier >= 2 ? 'background:var(--blue-dim);color:var(--blue)' : 'background:var(--bg-raised);color:var(--text-dim)') }}">
                                        T{{ $user->tier ?? 1 }}
                                    </span>
                                    <button type="button"
                                        onclick="openTierModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->tier ?? 1 }})"
                                        class="font-mono text-[10px] px-2 py-0.5 rounded transition"
                                        style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                                        Change
                                    </button>
                                </div>
                            </td>

                            {{-- Access level --}}
                            <td class="text-center">
                                @if ((int) ($user->access_level ?? 0) >= 1)
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:var(--green-dim);color:var(--green)">Verified</span>
                                @else
                                    <span class="font-mono text-[10px] px-2 py-0.5 rounded"
                                        style="background:rgba(251,191,36,0.1);color:#FBBF24">Demo</span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td class="font-mono text-[11px]" style="color:var(--text-muted)">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                @if ($user->id !== auth()->id())
                                    <button type="button"
                                        onclick="openDeleteUserModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="font-mono text-[11px] px-2 py-1 rounded transition"
                                        style="background:var(--red-dim);color:var(--red)">
                                        Delete
                                    </button>
                                @else
                                    <span class="font-mono text-[10px]" style="color:var(--text-dim)">You</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4" style="border-top:1px solid var(--border)">
            {{ $users->links() }}
        </div>
    </div>

    {{-- ═══════════════════════════
     TIER CHANGE MODAL
═══════════════════════════ --}}
    <div id="tierModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
        style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)">

        <div class="w-full max-w-sm rounded-2xl overflow-hidden"
            style="background:var(--bg-card);border:1px solid var(--border-lit);box-shadow:0 24px 64px rgba(0,0,0,0.6)">

            <div class="flex items-start gap-4 p-6" style="border-bottom:1px solid var(--border)">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:var(--blue-dim)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" style="color:var(--blue)">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[16px]" style="color:var(--text-white)">Change Tier</h3>
                    <p id="tier-modal-subtitle" class="text-[13px] mt-0.5" style="color:var(--text-muted)"></p>
                </div>
            </div>

            <div class="p-6">
                <p class="text-[12px] mb-4" style="color:var(--text-body)">
                    Select the new tier for this user. Changes take effect immediately.
                </p>

                <div class="space-y-2" id="tier-options">
                    @foreach ([1 => ['label' => 'Tier 1 — Free', 'desc' => 'Basic public access'], 2 => ['label' => 'Tier 2 — Standard', 'desc' => 'Registered user access'], 3 => ['label' => 'Tier 3 — Premium', 'desc' => 'Full intelligence access']] as $val => $info)
                        <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer transition tier-option"
                            data-tier="{{ $val }}"
                            style="border:1px solid var(--border);background:var(--bg-raised)">
                            <input type="radio" name="tier_select" value="{{ $val }}"
                                class="mt-0.5 flex-shrink-0 accent-amber-400">
                            <div>
                                <p class="text-[13px] font-semibold" style="color:var(--text-head)">
                                    {{ $info['label'] }}</p>
                                <p class="font-mono text-[10px]" style="color:var(--text-dim)">{{ $info['desc'] }}
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeTierModal()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Cancel
                </button>
                <button type="button" id="tier-confirm-btn"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition"
                    style="background:var(--blue);color:#080E1A">
                    Save Tier
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════
     DELETE USER MODAL
═══════════════════════════ --}}
    <div id="deleteUserModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
        style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)">

        <div class="w-full max-w-sm rounded-2xl overflow-hidden"
            style="background:var(--bg-card);border:1px solid var(--border-lit);box-shadow:0 24px 64px rgba(0,0,0,0.6)">

            <div class="flex items-start gap-4 p-6" style="border-bottom:1px solid var(--border)">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:var(--red-dim)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" style="color:var(--red)">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="23" y1="11" x2="17" y2="11" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-[16px]" style="color:var(--text-white)">Delete User</h3>
                    <p id="del-user-subtitle" class="text-[13px] mt-0.5" style="color:var(--text-muted)"></p>
                </div>
            </div>

            <div class="p-6">
                <p class="text-[13px] leading-relaxed" style="color:var(--text-body)">
                    This will permanently remove the user account. Their historical incident data will remain in the
                    database.
                </p>
                <p class="font-mono text-[11px] mt-3" style="color:var(--text-dim)">⚠ This action cannot be undone.
                </p>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeDeleteUserModal()"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition"
                    style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Cancel
                </button>
                <form id="deleteUserForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="del-user-btn"
                        class="w-full px-8 py-2.5 rounded-xl text-sm font-bold transition"
                        style="background:var(--red);color:#080E1A">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Toast ------------------------------------------------------------------
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const text = document.getElementById('toast-msg');
            icon.textContent = type === 'success' ? '✓' : '✕';
            text.textContent = msg;
            t.style.borderColor = type === 'success' ? 'var(--green)' : 'var(--red)';
            t.style.color = type === 'success' ? 'var(--green)' : 'var(--red)';
            t.classList.remove('hidden');
            t.classList.add('flex');
            setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('flex');
            }, 4000);
        }

        // ── Tier modal ------------------------------------------------------------
        let tierUserId = null;

        const tierBadgeStyles = {
            1: 'background:var(--bg-raised);color:var(--text-dim)',
            2: 'background:var(--blue-dim);color:var(--blue)',
            3: 'background:var(--amber-dim);color:var(--amber)',
        };

        function openTierModal(userId, userName, currentTier) {
            tierUserId = userId;
            document.getElementById('tier-modal-subtitle').textContent = `User: ${userName}`;

            // Pre-select current tier radio
            document.querySelectorAll('input[name="tier_select"]').forEach(r => {
                r.checked = parseInt(r.value) === parseInt(currentTier);
            });

            // Highlight selected option
            updateTierOptionHighlight();

            document.getElementById('tierModal').classList.remove('hidden');
            document.getElementById('tierModal').classList.add('flex');
        }

        function closeTierModal() {
            document.getElementById('tierModal').classList.add('hidden');
            document.getElementById('tierModal').classList.remove('flex');
            tierUserId = null;
        }

        function updateTierOptionHighlight() {
            document.querySelectorAll('.tier-option').forEach(opt => {
                const radio = opt.querySelector('input[type="radio"]');
                opt.style.borderColor = radio.checked ? 'var(--blue)' : 'var(--border)';
                opt.style.background = radio.checked ? 'var(--blue-dim)' : 'var(--bg-raised)';
            });
        }

        document.querySelectorAll('input[name="tier_select"]').forEach(r => {
            r.addEventListener('change', updateTierOptionHighlight);
        });

        document.getElementById('tier-confirm-btn').addEventListener('click', async function() {
            if (!tierUserId) return;

            const selectedRadio = document.querySelector('input[name="tier_select"]:checked');
            if (!selectedRadio) {
                showToast('Please select a tier.', 'error');
                return;
            }

            const newTier = parseInt(selectedRadio.value);
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Saving…';

            try {
                const res = await fetch(`/admin/users/${tierUserId}/tier`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        tier: newTier
                    }),
                });
                const data = await res.json();
                closeTierModal();

                if (data.success) {
                    showToast(data.message, 'success');

                    // Update the badge in-place without a full reload
                    const badge = document.getElementById('tier-badge-' + tierUserId);
                    if (badge) {
                        badge.textContent = 'T' + newTier;
                        badge.style.cssText = tierBadgeStyles[newTier] || tierBadgeStyles[1];
                    }
                } else {
                    showToast(data.message ?? 'Update failed.', 'error');
                }
            } catch (err) {
                closeTierModal();
                showToast('Request failed: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Tier';
            }
        });

        document.getElementById('tierModal').addEventListener('click', function(e) {
            if (e.target === this) closeTierModal();
        });

        // ── Delete user modal -----------------------------------------------------
        function openDeleteUserModal(userId, userName) {
            document.getElementById('del-user-subtitle').textContent = userName;
            document.getElementById('deleteUserForm').action = `/admin/users/${userId}`;
            document.getElementById('deleteUserModal').classList.remove('hidden');
            document.getElementById('deleteUserModal').classList.add('flex');
        }

        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.add('hidden');
            document.getElementById('deleteUserModal').classList.remove('flex');
        }

        document.getElementById('deleteUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteUserModal();
        });
    </script>

</x-admin-layout>
