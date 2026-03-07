import { Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ProjectHubComponent } from './pages/project-hub/project-hub.component';

export const routes: Routes = [
    { path: '', redirectTo: 'customer/161', pathMatch: 'full' },
    { path: 'dashboard', component: DashboardComponent },
    { path: 'customer/:id', component: DashboardComponent },
    { path: 'project-hub/:customerId/:projectId', component: ProjectHubComponent },
    { path: 'project/:id', component: ProjectHubComponent },
];
