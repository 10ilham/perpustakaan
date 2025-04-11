@extends('layouts.app')

@section('content')
<!-- MAIN -->
<main>
    <h1 class="title">Dashboard</h1>
    <ul class="breadcrumbs">
        <li><a href="#">Home</a></li>
        <li class="divider">/</li>
        <li><a href="#" class="active">Dashboard</a></li>
    </ul>
    <div class="info-data">
        <div class="card">
            <div class="head">
                <div>
                    <h2>1500</h2>
                    <p>Traffic</p>
                </div>
                <i class='bx bx-trending-up icon'></i>
            </div>
            <span class="progress" data-value="40%"></span>
            <span class="label">40%</span>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2>234</h2>
                    <p>Sales</p>
                </div>
                <i class='bx bx-trending-down icon down'></i>
            </div>
            <span class="progress" data-value="60%"></span>
            <span class="label">60%</span>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2>465</h2>
                    <p>Pageviews</p>
                </div>
                <i class='bx bx-trending-up icon'></i>
            </div>
            <span class="progress" data-value="30%"></span>
            <span class="label">30%</span>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2>235</h2>
                    <p>Visitors</p>
                </div>
                <i class='bx bx-trending-up icon'></i>
            </div>
            <span class="progress" data-value="80%"></span>
            <span class="label">80%</span>
        </div>
    </div>
    <div class="data">
        <div class="content-data">
            <div class="head">
                <h3>Sales Report</h3>
                <div class="menu">
                    <i class='bx bx-dots-horizontal-rounded icon'></i>
                    <ul class="menu-link">
                        <li><a href="#">Edit</a></li>
                        <li><a href="#">Save</a></li>
                        <li><a href="#">Remove</a></li>
                    </ul>
                </div>
            </div>
            <div class="chart">
                <div id="chart"></div>
            </div>
        </div>
        <div class="content-data">
            <div class="head">
                <h3>Chatbox</h3>
                <div class="menu">
                    <i class='bx bx-dots-horizontal-rounded icon'></i>
                    <ul class="menu-link">
                        <li><a href="#">Edit</a></li>
                        <li><a href="#">Save</a></li>
                        <li><a href="#">Remove</a></li>
                    </ul>
                </div>
            </div>
            <div class="chat-box">
                <p class="day"><span>Today</span></p>
                <div class="msg">
                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8cGVvcGxlfGVufDB8fDB8fA%3D%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="">
                    <div class="chat">
                        <div class="profile">
                            <span class="username">Alan</span>
                            <span class="time">18:30</span>
                        </div>
                        <p>Hello</p>
                    </div>
                </div>
                <div class="msg me">
                    <div class="chat">
                        <div class="profile">
                            <span class="time">18:30</span>
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eaque voluptatum eos quam dolores eligendi exercitationem animi nobis reprehenderit laborum! Nulla.</p>
                    </div>
                </div>
                <div class="msg me">
                    <div class="chat">
                        <div class="profile">
                            <span class="time">18:30</span>
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam, architecto!</p>
                    </div>
                </div>
                <div class="msg me">
                    <div class="chat">
                        <div class="profile">
                            <span class="time">18:30</span>
                        </div>
                        <p>Lorem ipsum, dolor sit amet.</p>
                    </div>
                </div>
            </div>
            <form action="#">
                <div class="form-group">
                    <input type="text" placeholder="Type...">
                    <button type="submit" class="btn-send"><i class='bx bxs-send'></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Status -->
    <div class="row ">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Order Status</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check form-check-muted m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            </label>
                                        </div>
                                    </th>
                                    <th> Client Name </th>
                                    <th> Order No </th>
                                    <th> Product Cost </th>
                                    <th> Project </th>
                                    <th> Payment Mode </th>
                                    <th> Start Date </th>
                                    <th> Payment Status </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-muted m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="assets/images/faces/face1.jpg" alt="image" />
                                        <span class="pl-2">Henry Klein</span>
                                    </td>
                                    <td> 02312 </td>
                                    <td> $14,500 </td>
                                    <td> Dashboard </td>
                                    <td> Credit card </td>
                                    <td> 04 Dec 2019 </td>
                                    <td>
                                        <div class="badge badge-outline-success">Approved</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-muted m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="assets/images/faces/face2.jpg" alt="image" />
                                        <span class="pl-2">Estella Bryan</span>
                                    </td>
                                    <td> 02312 </td>
                                    <td> $14,500 </td>
                                    <td> Website </td>
                                    <td> Cash on delivered </td>
                                    <td> 04 Dec 2019 </td>
                                    <td>
                                        <div class="badge badge-outline-warning">Pending</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-muted m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="assets/images/faces/face5.jpg" alt="image" />
                                        <span class="pl-2">Lucy Abbott</span>
                                    </td>
                                    <td> 02312 </td>
                                    <td> $14,500 </td>
                                    <td> App design </td>
                                    <td> Credit card </td>
                                    <td> 04 Dec 2019 </td>
                                    <td>
                                        <div class="badge badge-outline-danger">Rejected</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- END MAIN -->
@endsection
