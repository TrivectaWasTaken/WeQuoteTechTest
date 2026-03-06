import { Component, OnInit, signal, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule, Router } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { Project, Stats } from '../../models/project.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-project-hub',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './project-hub.component.html',
  styleUrls: ['./project-hub.component.css']
})
export class ProjectHubComponent implements OnInit, OnDestroy {
  projectId = signal<number|null>(null);
  customerId = signal<string|null>(null);
  project = signal<Project|null>(null);
  stats = signal<Stats|null>(null);
  invoices = signal<any[]>([]);
  quotes = signal<any[]>([]);
  activeTab = signal<'dashboard' | 'invoices' | 'quotes'>('dashboard');
  private subscriptions = new Subscription();

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private projectService: ProjectService
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(this.route.params.subscribe(params => {
      const projectId = params['projectId'];
      const customerId = params['customerId'];

      if (customerId) {
        this.customerId.set(customerId);
        this.subscriptions.add(this.projectService.getStats('1', customerId).subscribe(data => this.stats.set(data)));
      }

      if (projectId) {
        this.projectId.set(+projectId);
        this.loadProjectDetails(+projectId);
      }
    }));

    this.subscriptions.add(this.route.queryParams.subscribe(params => {
        if (params['tab']) {
            this.activeTab.set(params['tab']);
        }
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  loadProjectDetails(id: number): void {
    this.projectService.getProject(id).subscribe(data => {
      this.project.set(data);
    });

    this.projectService.getInvoices(id.toString()).subscribe(data => {
      this.invoices.set(data);
    });

    this.projectService.getQuotes(id.toString()).subscribe(data => {
      this.quotes.set(data);
    });
  }

  setTab(tab: 'dashboard' | 'invoices' | 'quotes'): void {
    this.activeTab.set(tab);
    // Update URL query param using Router for cleaner state management in Angular
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { tab: tab },
      queryParamsHandling: 'merge',
      replaceUrl: true
    });
  }
}
