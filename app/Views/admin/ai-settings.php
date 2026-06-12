<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-robot" style="color:var(--primary);margin-right:12px;"></i>AI Settings</h1>
                <p>Configure the AI chatbot, API key, and knowledge base</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="profile-layout" style="gap:32px;">
            <div class="profile-form-card">
                <div class="form-section-title">OpenRouter Configuration</div>
                <form method="POST" action="/admin/ai-settings">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-key"></i> API Key</label>
                        <input type="password" name="openrouter_api_key" class="form-input" value="<?= htmlspecialchars($apiKey) ?>" placeholder="sk-or-v1-...">
                        <span class="form-hint">Stored encrypted in the database. Leave as-is to keep current.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-cube"></i> Model</label>
                        <select name="openrouter_model" class="form-select">
                            <option value="openai/gpt-oss-120b:free" <?= $model === 'openai/gpt-oss-120b:free' ? 'selected' : '' ?>>OpenAI GPT OSS 120B (Free)</option>
                            <option value="openai/gpt-4o-mini" <?= $model === 'openai/gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini</option>
                            <option value="anthropic/claude-3-haiku" <?= $model === 'anthropic/claude-3-haiku' ? 'selected' : '' ?>>Claude 3 Haiku</option>
                            <option value="google/gemini-2.0-flash-001" <?= $model === 'google/gemini-2.0-flash-001' ? 'selected' : '' ?>>Gemini 2.0 Flash</option>
                            <option value="meta-llama/llama-3.3-70b-instruct" <?= $model === 'meta-llama/llama-3.3-70b-instruct' ? 'selected' : '' ?>>Llama 3.3 70B</option>
                        </select>
                    </div>

                    <div class="form-section" style="margin-top:24px;">
                        <div class="form-section-title">RAG (Knowledge Base) Settings</div>

                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="rag_enabled" value="1" <?= $ragEnabled === '1' ? 'checked' : '' ?>>
                                Enable RAG (Retrieval-Augmented Generation)
                            </label>
                            <span class="form-hint">When enabled, the AI will search the knowledge base for relevant context before responding.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Chunk Size</label>
                            <input type="number" name="rag_chunk_size" class="form-input" value="<?= htmlspecialchars($ragChunkSize) ?>" min="100" max="2000" style="width:120px;">
                            <span class="form-hint">Max characters per document chunk sent to the AI.</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                    </div>
                </form>
            </div>

            <div class="profile-form-card">
                <div class="form-section-title">Knowledge Base Documents</div>
                <p class="form-section-desc">Add documents that the AI can reference when answering questions.</p>

                <form method="POST" action="/admin/rag-documents" style="margin-bottom:24px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="create">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input" required placeholder="e.g. Clinic Hours">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-textarea" rows="5" required placeholder="The knowledge content the AI should reference..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Document</button>
                    </div>
                </form>

                <?php if (!empty($documents)): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Source</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['title']) ?></td>
                                    <td><?= htmlspecialchars($doc['source'] ?? 'manual') ?></td>
                                    <td>
                                        <form method="POST" action="/admin/rag-documents" style="display:inline" onsubmit="return confirm('Remove this document from the knowledge base?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No documents yet. Add your first knowledge base entry above.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
