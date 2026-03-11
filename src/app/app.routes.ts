import { Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ProjectHubComponent } from './pages/project-hub/project-hub.component';
import { InvoicesComponent } from './pages/invoices/invoices.component';
import { QuotesComponent } from './pages/quotes/quotes.component';
import { RecentActivityComponent } from './pages/project-hub/components/recent-activity/recent-activity.component';

export const routes: Routes = [
  { path: '', redirectTo: 'customer/161', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'customer/:id', component: DashboardComponent },
  { path: 'project-hub/:customerId/:projectId', component: ProjectHubComponent },
  { path: 'project-hub/:customerId/:projectId/invoices', component: InvoicesComponent },
  { path: 'project-hub/:customerId/:projectId/quotes', component: QuotesComponent },
  { path: 'project/:id', component: ProjectHubComponent },
];
