/**
 * Export Performance Display Module
 * 
 * Handles the display of detailed export performance statistics
 * in a user-friendly format during and after export operations
 */

class ExportPerformanceDisplay {
    constructor() {
        this.currentExportId = null;
        this.performanceContainer = null;
        this.init();
    }
    
    init() {
        // Create performance display container if it doesn't exist
        this.createPerformanceContainer();
        
        // Set up event listeners
        this.setupEventListeners();
    }
    
    createPerformanceContainer() {
        // Check if container already exists
        this.performanceContainer = document.getElementById('export-performance-stats');
        
        if (!this.performanceContainer) {
            // Create the performance stats container
            const container = document.createElement('div');
            container.id = 'export-performance-stats';
            container.className = 'export-performance-container';
            container.style.display = 'none';
            
            // Find a good place to insert it (after export controls)
            const exportSection = document.querySelector('.export-section') || document.querySelector('.card');
            if (exportSection) {
                exportSection.appendChild(container);
                this.performanceContainer = container;
            }
        }
    }
    
    setupEventListeners() {
        // Listen for export completion events
        document.addEventListener('exportCompleted', (event) => {
            this.displayPerformanceStats(event.detail);
        });
        
        // Listen for export progress events
        document.addEventListener('exportProgress', (event) => {
            this.updateProgressDisplay(event.detail);
        });
    }
    
    /**
     * Display comprehensive performance statistics
     */
    displayPerformanceStats(exportResult) {
        if (!exportResult.performanceStats) {
            return;
        }
        
        const stats = exportResult.performanceStats;
        const isChunked = exportResult.exportStrategy === 'chunked';
        
        const html = `
            <div class="performance-stats-card">
                <h5 class="performance-title">
                    <i class="fas fa-chart-line"></i>
                    Export Performance Report
                    <span class="performance-badge ${this.getPerformanceBadgeClass(stats)}">${this.getPerformanceGrade(stats)}</span>
                </h5>
                
                <div class="performance-grid">
                    ${this.renderProcessingTimeStats(stats)}
                    ${this.renderThroughputStats(stats)}
                    ${this.renderMemoryStats(stats)}
                    ${this.renderEfficiencyStats(stats)}
                    ${isChunked ? this.renderChunkingStats(stats) : ''}
                </div>
                
                <div class="performance-summary">
                    <p class="performance-message">
                        <strong>${exportResult.message}</strong>
                    </p>
                    ${this.renderPerformanceInsights(stats, exportResult)}
                </div>
                
                <div class="performance-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportPerformance.toggleDetails()">
                        <i class="fas fa-info-circle"></i> View Details
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportPerformance.downloadReport('${exportResult.exportId}')">
                        <i class="fas fa-download"></i> Download File${isChunked ? 's' : ''}
                    </button>
                </div>
            </div>
        `;
        
        this.performanceContainer.innerHTML = html;
        this.performanceContainer.style.display = 'block';
        
        // Add smooth animation
        this.performanceContainer.style.opacity = '0';
        setTimeout(() => {
            this.performanceContainer.style.opacity = '1';
        }, 100);
    }
    
    renderProcessingTimeStats(stats) {
        if (!stats.processingTime) return '';
        
        return `
            <div class="stat-card processing-time">
                <div class="stat-icon">
                    <i class="fas fa-clock text-primary"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">${stats.processingTime.formatted}</div>
                    <div class="stat-label">Processing Time</div>
                    <div class="stat-detail">${stats.processingTime.total_seconds} seconds</div>
                </div>
            </div>
        `;
    }
    
    renderThroughputStats(stats) {
        if (!stats.throughput) return '';
        
        const performanceClass = stats.throughput.records_per_second > 1000 ? 'text-success' : 
                                stats.throughput.records_per_second > 500 ? 'text-warning' : 'text-danger';
        
        return `
            <div class="stat-card throughput">
                <div class="stat-icon">
                    <i class="fas fa-tachometer-alt ${performanceClass}"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">${stats.throughput.formatted}</div>
                    <div class="stat-label">Throughput</div>
                    <div class="stat-detail">${stats.throughput.records_per_second} rec/sec</div>
                </div>
            </div>
        `;
    }
    
    renderMemoryStats(stats) {
        if (!stats.memory) return '';
        
        const memoryClass = stats.memory.used_mb < 50 ? 'text-success' : 
                           stats.memory.used_mb < 100 ? 'text-warning' : 'text-danger';
        
        return `
            <div class="stat-card memory">
                <div class="stat-icon">
                    <i class="fas fa-memory ${memoryClass}"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">${stats.memory.used_mb} MB</div>
                    <div class="stat-label">Memory Used</div>
                    <div class="stat-detail">${stats.memory.formatted}</div>
                </div>
            </div>
        `;
    }
    
    renderEfficiencyStats(stats) {
        if (!stats.efficiency) return '';
        
        return `
            <div class="stat-card efficiency">
                <div class="stat-icon">
                    <i class="fas fa-chart-pie text-info"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">${stats.efficiency.size_per_record || 'N/A'}</div>
                    <div class="stat-label">Efficiency</div>
                    <div class="stat-detail">${stats.efficiency.time_per_record || 'N/A'}</div>
                    ${stats.efficiency.compression ? `<div class="stat-compression">Compression: ${stats.efficiency.compression}</div>` : ''}
                </div>
            </div>
        `;
    }
    
    renderChunkingStats(stats) {
        if (!stats.chunking) return '';
        
        return `
            <div class="stat-card chunking">
                <div class="stat-icon">
                    <i class="fas fa-layer-group text-purple"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">${stats.chunking.formatted}</div>
                    <div class="stat-label">Chunk Processing</div>
                    <div class="stat-detail">Min: ${stats.chunking.min_time}s | Max: ${stats.chunking.max_time}s</div>
                </div>
            </div>
        `;
    }
    
    renderPerformanceInsights(stats, exportResult) {
        const insights = [];
        
        // Performance insights based on metrics
        if (stats.throughput && stats.throughput.records_per_second > 2000) {
            insights.push('🚀 Excellent throughput performance');
        } else if (stats.throughput && stats.throughput.records_per_second < 500) {
            insights.push('⚠️ Consider using chunked export for better performance');
        }
        
        if (stats.memory && stats.memory.used_mb < 30) {
            insights.push('💚 Memory efficient processing');
        }
        
        if (stats.efficiency && stats.efficiency.compression) {
            const compressionPercent = parseInt(stats.efficiency.compression);
            if (compressionPercent > 70) {
                insights.push('🗜️ Excellent compression ratio');
            }
        }
        
        if (exportResult.exportStrategy === 'chunked') {
            insights.push(`📦 Split into ${exportResult.totalFiles} manageable files`);
        }
        
        if (insights.length === 0) {
            insights.push('✅ Export completed successfully');
        }
        
        return `
            <div class="performance-insights">
                ${insights.map(insight => `<div class="insight-item">${insight}</div>`).join('')}
            </div>
        `;
    }
    
    getPerformanceGrade(stats) {
        let score = 0;
        
        // Throughput scoring
        if (stats.throughput) {
            if (stats.throughput.records_per_second > 2000) score += 30;
            else if (stats.throughput.records_per_second > 1000) score += 20;
            else if (stats.throughput.records_per_second > 500) score += 10;
        }
        
        // Memory scoring
        if (stats.memory) {
            if (stats.memory.used_mb < 30) score += 25;
            else if (stats.memory.used_mb < 50) score += 20;
            else if (stats.memory.used_mb < 100) score += 10;
        }
        
        // Efficiency scoring
        if (stats.efficiency) {
            if (stats.efficiency.time_per_record && parseFloat(stats.efficiency.time_per_record) < 1.0) score += 25;
            else if (stats.efficiency.time_per_record && parseFloat(stats.efficiency.time_per_record) < 2.0) score += 15;
            
            if (stats.efficiency.compression) {
                const compressionPercent = parseInt(stats.efficiency.compression);
                if (compressionPercent > 70) score += 20;
                else if (compressionPercent > 50) score += 10;
            }
        }
        
        // Grade assignment
        if (score >= 80) return 'A+';
        if (score >= 70) return 'A';
        if (score >= 60) return 'B+';
        if (score >= 50) return 'B';
        if (score >= 40) return 'C';
        return 'D';
    }
    
    getPerformanceBadgeClass(stats) {
        const grade = this.getPerformanceGrade(stats);
        if (['A+', 'A'].includes(grade)) return 'badge-success';
        if (['B+', 'B'].includes(grade)) return 'badge-warning';
        return 'badge-danger';
    }
    
    /**
     * Update progress display during export
     */
    updateProgressDisplay(progress) {
        // Show progress indicator during processing
        if (progress.status === 'processing') {
            const html = `
                <div class="export-progress-display">
                    <div class="progress-header">
                        <h6><i class="fas fa-cog fa-spin"></i> Export in Progress...</h6>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: ${progress.percentage || 0}%">
                            ${progress.percentage || 0}%
                        </div>
                    </div>
                    <div class="progress-details">
                        <small class="text-muted">
                            ${progress.message || 'Processing your export request...'}
                        </small>
                    </div>
                </div>
            `;
            
            this.performanceContainer.innerHTML = html;
            this.performanceContainer.style.display = 'block';
        }
    }
    
    /**
     * Toggle detailed performance view
     */
    toggleDetails() {
        const detailsContainer = document.querySelector('.performance-details');
        if (detailsContainer) {
            detailsContainer.style.display = detailsContainer.style.display === 'none' ? 'block' : 'none';
        }
    }
    
    /**
     * Download the exported file(s)
     */
    downloadReport(exportId) {
        // Redirect to download endpoint
        window.location.href = `/ybb-export/download/${exportId}`;
    }
    
    /**
     * Hide performance display
     */
    hide() {
        if (this.performanceContainer) {
            this.performanceContainer.style.display = 'none';
        }
    }
}

// Initialize the performance display module
const exportPerformance = new ExportPerformanceDisplay();

// CSS Styles for performance display
const performanceStyles = `
<style>
.export-performance-container {
    margin-top: 20px;
    transition: opacity 0.3s ease;
}

.performance-stats-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.performance-title {
    margin-bottom: 20px;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.performance-title i {
    margin-right: 8px;
    color: #007bff;
}

.performance-badge {
    font-size: 0.8em;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: bold;
}

.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: #212529; }
.badge-danger { background: #dc3545; color: white; }

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-icon {
    margin-right: 15px;
    font-size: 1.5em;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.2em;
    font-weight: bold;
    color: #495057;
}

.stat-label {
    font-size: 0.9em;
    color: #6c757d;
    margin-bottom: 2px;
}

.stat-detail {
    font-size: 0.8em;
    color: #adb5bd;
}

.stat-compression {
    font-size: 0.8em;
    color: #28a745;
    margin-top: 2px;
}

.performance-summary {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-bottom: 15px;
}

.performance-message {
    margin: 0;
    color: #495057;
}

.performance-insights {
    margin-top: 10px;
}

.insight-item {
    font-size: 0.9em;
    color: #6c757d;
    margin-bottom: 5px;
}

.performance-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.export-progress-display {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.progress-header h6 {
    margin: 0;
    color: #495057;
}

.text-purple { color: #6f42c1 !important; }

@media (max-width: 768px) {
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-actions {
        flex-direction: column;
    }
}
</style>
`;

// Inject CSS styles
document.head.insertAdjacentHTML('beforeend', performanceStyles);
