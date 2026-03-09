<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Onboarding - Shoe ERP</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap');
        
        :root {
            --brand-dark: #0f172a;       /* Slate 900 */
            --brand-light: #ffffff;
            --accent: #2563eb;           /* Professional Blue */
            --accent-hover: #1d4ed8;
            --bg-base: #f8fafc;          /* Slate 50 */
            --text-main: #0f172a;
            --text-muted: #64748b;       /* Slate 500 */
            --border-light: #e2e8f0;     /* Slate 200 */
            --border-strong: #cbd5e1;    /* Slate 300 */
            --success: #10b981;
            --warning: #f59e0b;
            --radius-md: 8px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: var(--bg-base); color: var(--text-main); line-height: 1.5; display: flex; flex-direction: column; min-height: 100vh; }

        /* Minimal Header */
        .topbar { background: var(--brand-light); padding: 16px 40px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; letter-spacing: -0.5px; }
        .logo-icon { width: 32px; height: 32px; background: var(--brand-dark); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        
        .header-status { display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .secure-badge { display: flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #a7f3d0; }

        /* Main Workspace Container */
        .workspace { max-width: 1100px; margin: 40px auto; padding: 0 24px; width: 100%; flex: 1; }
        
        .page-header { margin-bottom: 32px; }
        .page-title { font-size: 28px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 8px; }
        .page-desc { font-size: 15px; color: var(--text-muted); }

        /* Split Grid Layout */
        .onboarding-grid { display: grid; grid-template-columns: 320px 1fr; gap: 32px; align-items: start; }

        /* LEFT COLUMN: Identity & Documents */
        .profile-card { background: var(--brand-light); border-radius: var(--radius-lg); border: 1px solid var(--border-light); padding: 32px 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); text-align: center; position: sticky; top: 100px; }
        
        /* The Image Upload UI (image_doc & image_file_name) */
        .avatar-upload { width: 140px; height: 140px; border-radius: 50%; border: 2px dashed var(--border-strong); margin: 0 auto 24px auto; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-base); cursor: pointer; transition: 0.2s; position: relative; overflow: hidden; }
        .avatar-upload:hover { border-color: var(--accent); background: #eff6ff; }
        .upload-icon { font-size: 24px; color: var(--text-muted); margin-bottom: 8px; }
        .upload-text { font-size: 11px; font-weight: 600; color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .profile-sys-info { margin-top: 32px; text-align: left; padding-top: 24px; border-top: 1px solid var(--border-light); }
        .sys-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 12px; }
        .sys-lbl { color: var(--text-muted); font-weight: 500; }
        .sys-val { color: var(--text-main); font-family: 'JetBrains Mono', monospace; font-weight: 600; }
        
        /* Verification Status Badge (verify_status_id = 2) */
        .verify-status { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; color: #b45309; font-size: 12px; font-weight: 700; gap: 8px; margin-top: 16px; }

        /* RIGHT COLUMN: Form Categories */
        .form-section { display: flex; flex-direction: column; gap: 24px; }
        
        .form-card { background: var(--brand-light); border-radius: var(--radius-lg); border: 1px solid var(--border-light); padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .card-title { font-size: 16px; font-weight: 700; color: var(--text-main); border-bottom: 1px solid var(--border-light); padding-bottom: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }

        /* Grid for Inputs */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .col-span-full { grid-column: 1 / -1; }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-label { font-size: 13px; font-weight: 600; color: #334155; }
        .form-label span { color: #ef4444; } /* Required Asterisk */
        
        /* Ultra-crisp inputs */
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-strong); border-radius: var(--radius-md); font-size: 14px; color: var(--text-main); background: var(--brand-light); outline: none; transition: 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.01); }
        .form-control::placeholder { color: #94a3b8; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        select.form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 12px center; }
        textarea.form-control { resize: vertical; min-height: 80px; }

        /* Username styling (user_name) */
        .username-input { font-family: 'JetBrains Mono', monospace; font-weight: 600; letter-spacing: -0.5px; background: #f8fafc; }

        /* Footer Actions */
        .action-footer { margin-top: 32px; display: flex; justify-content: flex-end; gap: 16px; padding-top: 24px; border-top: 1px solid var(--border-strong); }
        .btn { padding: 12px 24px; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; display: flex; align-items: center; gap: 8px; }
        .btn-outline { background: var(--brand-light); border: 1px solid var(--border-strong); color: var(--text-main); }
        .btn-outline:hover { background: var(--bg-base); }
        .btn-primary { background: var(--accent); color: white; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2); }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3); }

    </style>
</head>
<body>

    <header class="topbar">
        <div class="logo">
            <div class="logo-icon">SE</div> Shoe ERP
        </div>
        <div class="header-status">
            <div class="secure-badge">✓ 256-Bit Encrypted</div>
            System Admin
        </div>
    </header>

    <main class="workspace">
        <div class="page-header">
            <h1 class="page-title">Register New System User</h1>
            <p class="page-desc">Complete the employee profile. Submission requires administrator verification before system access is granted.</p>
        </div>

        <form class="onboarding-grid">
            
            <div class="profile-card">
                <div class="avatar-upload">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">Upload Photo</div>
                </div>
                
                <div style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">New User Profile</div>
                <div style="font-size: 13px; color: var(--text-muted);">Employee / Operator</div>

                <div class="verify-status">
                    ⏳ Pending Verification
                </div>

                <div class="profile-sys-info">
                    <div class="sys-row">
                        <span class="sys-lbl">Date of Reg:</span>
                        <span class="sys-val">08 Mar 2026</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-lbl">Registration IP:</span>
                        <span class="sys-val" style="color: var(--accent);">192.168.1.45</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-lbl">Account Status:</span>
                        <span class="sys-val">Draft / Inactive</span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                
                <div class="form-card">
                    <div class="card-title">👤 Personal Information</div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">First Name (f_name) <span>*</span></label>
                            <input type="text" class="form-control" placeholder="e.g. Rahul" required autofocus>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name (l_name) <span>*</span></label>
                            <input type="text" class="form-control" placeholder="e.g. Sharma" required>
                        </div>
                        
                        <div class="form-group col-span-full">
                            <label class="form-label">System Username (user_name) <span>*</span></label>
                            <input type="text" class="form-control username-input" placeholder="e.g. rahul.sharma or EMP-1045" required>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">This will be used for ERP login.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date of Birth (dob)</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender (gender)</label>
                            <select class="form-control">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="card-title">📞 Contact & Location Details</div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Mobile Number (mobile) <span>*</span></label>
                            <input type="tel" class="form-control" placeholder="+91 00000 00000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address (email)</label>
                            <input type="email" class="form-control" placeholder="employee@shoeerp.com">
                        </div>

                        <div class="form-group col-span-full">
                            <label class="form-label">Residential Address (address)</label>
                            <textarea class="form-control" placeholder="Enter complete street address..."></textarea>
                        </div>
                    </div>

                    <div class="grid-3" style="margin-top: 20px;">
                        <div class="form-group">
                            <label class="form-label">State (state_id)</label>
                            <select class="form-control">
                                <option value="">Select State</option>
                                <option value="1">West Bengal</option>
                                <option value="2">Maharashtra</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District (district_id)</label>
                            <select class="form-control">
                                <option value="">Select District</option>
                                <option value="1">Kolkata</option>
                                <option value="2">Howrah</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PIN Code (pin)</label>
                            <input type="text" class="form-control" placeholder="e.g. 700091">
                        </div>
                    </div>
                </div>

                <div class="action-footer">
                    <button type="button" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Profile & Submit for Verification ➔</button>
                </div>

            </div>
        </form>
    </main>

</body>
</html>