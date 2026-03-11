import { Component, Input, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Project } from '../../../../models/project.model';

@Component({
  selector: 'app-recent-activity',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="bg-white rounded-[2rem] shadow-[0_8px_40px_rgb(0,0,0,0.03)] border border-gray-100 overflow-hidden min-h-[400px]">
        <div class="p-10">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h4 class="text-2xl font-black text-[#000a2d] tracking-tight">Recent Project Activity</h4>
                    <p class="text-gray-400 font-bold text-sm mt-1 uppercase tracking-widest">Click a card above to view full details</p>
                </div>
            </div>

            <div class="h-64 flex flex-col items-center justify-center space-y-6 text-center">
                <div class="w-20 h-20 bg-[#f8fafc] rounded-full flex items-center justify-center text-gray-200">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-gray-400 font-bold text-sm">Select a category from the tiles above to manage your project assets.</p>
            </div>
        </div>
    </div>
  `
})
export class RecentActivityComponent {
  @Input() project = signal<Project|null>(null);
}
