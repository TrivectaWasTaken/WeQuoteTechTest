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
  activeTab = signal<string>('overview');
  invoices = signal<any[]>([]);
  quotes = signal<any[]>([]);
  projectId = signal<number|null>(null);
  customerId = signal<string|null>(null);
  project = signal<Project|null>(null);
  stats = signal<Stats|null>(null);
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
        this.projectService.getInvoices(projectId).subscribe(data => this.invoices.set(data));
        this.projectService.getQuotes(projectId).subscribe(data => this.quotes.set(data));
      }
    }));
  }

  setTab(tab: string): void {
    this.activeTab.set(tab);
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  loadProjectDetails(id: number): void {
    this.projectService.getProject(id).subscribe(data => {
      this.project.set(data);
    });
  }

  getPaidWidth(): number {
    const p = this.project();
    if (!p || !p.net_total) return 0;
    return (p.paid_total / p.net_total) * 100;
  }

  getOutstandingWidth(): number {
    const p = this.project();
    if (!p || !p.net_total) return 0;
    return ((p.outstanding_total ?? 0) / p.net_total) * 100;
  }
}
