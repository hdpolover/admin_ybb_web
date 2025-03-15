<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Dashboard')); ?>
    
    <!-- jsvectormap css -->
    <link href="<?= base_url('assets/libs/jsvectormap/jsvectormap.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- apexcharts -->
    <link href="<?= base_url('assets/libs/apexcharts/apexcharts.min.css') ?>" rel="stylesheet" type="text/css" />

    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Dashboard', 'title' => esc($program->name) . ' Dashboard')); ?>

                    <!-- Summary Widgets -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                                <i class="ri-user-3-line text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_participants) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <i class="ri-user-add-line align-middle"></i> <?= number_format($summary->participants_today) ?> today
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded-circle fs-3">
                                                <i class="ri-team-line text-warning"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Ambassadors</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_ambassadors) ?></h4>
                                            <p class="text-muted mb-0">Active ambassadors</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded-circle fs-3">
                                                <i class="ri-link text-success"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Referred Participants</p>
                                            <h4 class="fs-4 flex-grow-1 mb-1"><?= number_format($summary->total_referred) ?></h4>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-light text-success">
                                                    <?= $summary->referral_percentage ?>% of total
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-3">
                                                <i class="ri-calendar-event-line text-info"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden ms-3">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Program Status</p>
                                            <?php if($program->is_active == 1): ?>
                                                <h4 class="fs-5 flex-grow-1 mb-1 text-success">Active</h4>
                                                <p class="text-muted mb-0">
                                                    <?= $program->start_date ?>
                                                </p>
                                            <?php else: ?>
                                                <h4 class="fs-5 flex-grow-1 mb-1 text-danger">Inactive</h4>
                                                <p class="text-muted mb-0">Program not active</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Registration Chart -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Registration Trend</h4>
                                    <div>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="day">
                                            Daily
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="week">
                                            Weekly
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary btn-sm" data-period="month">
                                            Monthly
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <div id="registration-chart" class="apex-charts" dir="ltr" style="height: 360px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Gender Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <div id="gender-chart" class="apex-charts" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Age and Nationality -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Age Distribution</h4>
                                </div>
                                <div class="card-body">
                                    <div id="age-chart" class="apex-charts" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Nationalities</h4>
                                </div>
                                <div class="card-body">
                                    <div id="nationality-chart" class="apex-charts" style="height: 350px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ambassador Performance -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top Ambassadors</h4>
                                    <div class="flex-shrink-0">
                                        <a href="<?= site_url('ambassadors') ?>" class="btn btn-soft-info btn-sm">
                                            View All Ambassadors
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ambassador</th>
                                                    <th class="text-end">Participants Referred</th>
                                                    <th class="text-end">Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ambassadorStats as $ambassador): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2">
                                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                    <?= strtoupper(substr($ambassador->ambassador_name, 0, 1)) ?>
                                                                </span>
                                                            </div>
                                                            <span><?= esc($ambassador->ambassador_name) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end"><?= number_format($ambassador->total_referrals) ?></td>
                                                    <td class="text-end">
                                                        <?php 
                                                        $percentage = 0;
                                                        if ($summary->total_participants > 0) {
                                                            $percentage = ($ambassador->total_referrals / $summary->total_participants) * 100;
                                                        }
                                                        ?>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                                        </div>
                                                        <span class="small"><?= number_format($percentage, 1) ?>% of total</span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($ambassadorStats)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">No ambassador data available</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- apexcharts -->
    <script src="<?= base_url('assets/libs/apexcharts/apexcharts.min.js') ?>"></script>

    <!-- Dashboard charts js -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // For debugging
        console.log('Gender Data:', <?= $genderChartData ?>);
        console.log('Nationality Data:', <?= $nationalityChartData ?>);

        // Registration Chart
        var registrationOptions = {
            chart: {
                height: 360,
                type: 'area',
                toolbar: {
                    show: false,
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            series: [{
                name: 'Registrations',
                data: <?= $registrationChartData ?>.values
            }],
            xaxis: {
                categories: <?= $registrationChartData ?>.labels,
                title: {
                    text: 'Date'
                }
            },
            yaxis: {
                title: {
                    text: 'Number of Registrations'
                },
            },
            colors: ['#1c84ee'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                },
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " participants"
                    }
                }
            }
        };
        var registrationChart = new ApexCharts(
            document.querySelector("#registration-chart"),
            registrationOptions
        );
        registrationChart.render();

        // Gender Chart
        var genderData = <?= $genderChartData ?>;
        var genderOptions = {
            chart: {
                height: 370,
                type: 'pie',
            },
            series: genderData.values,
            labels: genderData.labels,
            colors: ["#038edc", "#f7b84b", "#51d28c", "#f34e4e", "#564ab1"],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                    }
                }
            },
            noData: {
                text: 'No gender data available',
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: "#6c757d",
                    fontSize: '16px',
                    fontFamily: "Poppins"
                }
            }
        };
        var genderChart = new ApexCharts(
            document.querySelector("#gender-chart"),
            genderOptions
        );
        genderChart.render();

        // Age Chart
        var ageOptions = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            dataLabels: {
                enabled: false
            },
            series: [{
                name: 'Participants',
                data: <?= $ageChartData ?>.values
            }],
            xaxis: {
                categories: <?= $ageChartData ?>.labels,
                title: {
                    text: 'Number of Participants'
                }
            },
            colors: ['#564ab1']
        };
        var ageChart = new ApexCharts(
            document.querySelector("#age-chart"),
            ageOptions
        );
        ageChart.render();

        // Nationality Chart
        var nationalityData = <?= $nationalityChartData ?>;
        var nationalityOptions = {
            series: [{
                name: 'Participants',
                data: nationalityData.values
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                    distributed: true
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#51d28c', '#f1734f', '#038edc', '#564ab1', '#f7b84b', '#51d28c', '#f34e4e'],
            xaxis: {
                categories: nationalityData.labels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Number of Participants'
                }
            },
            noData: {
                text: 'No nationality data available',
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: "#6c757d",
                    fontSize: '16px',
                    fontFamily: "Poppins"
                }
            }
        };
        var nationalityChart = new ApexCharts(
            document.querySelector("#nationality-chart"),
            nationalityOptions
        );
        nationalityChart.render();

        // Period filter buttons
        document.querySelectorAll('button[data-period]').forEach(function(button) {
            button.addEventListener('click', function() {
                var period = this.getAttribute('data-period');
                
                // Set active class
                document.querySelectorAll('button[data-period]').forEach(function(btn) {
                    btn.classList.remove('active', 'btn-secondary');
                    btn.classList.add('btn-soft-secondary');
                });
                this.classList.remove('btn-soft-secondary');
                this.classList.add('active', 'btn-secondary');
                
                // Show loading state
                registrationChart.updateOptions({
                    chart: {
                        animations: {
                            enabled: false
                        }
                    }
                });

                // Fetch data for selected period
                fetch('<?= site_url('dashboard/ajaxRegistrationStats') ?>?period=' + period)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Period data:', data);
                        
                        // Update chart with new data
                        registrationChart.updateOptions({
                            xaxis: {
                                categories: data.labels
                            },
                            series: [{
                                name: 'Registrations',
                                data: data.values
                            }],
                            chart: {
                                animations: {
                                    enabled: true
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching registration data:', error);
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Load Error',
                            text: 'Failed to load registration statistics',
                            confirmButtonText: 'OK'
                        });
                    });
            });
        });
        
        // Set first button as active
        document.querySelector('button[data-period="day"]').classList.add('active', 'btn-secondary');
        document.querySelector('button[data-period="day"]').classList.remove('btn-soft-secondary');
    });
    </script>

    <!-- App js -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>

</html>